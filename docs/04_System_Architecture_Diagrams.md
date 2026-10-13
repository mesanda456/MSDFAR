# Department of Fisheries and Aquatic Resources (DFAR)
## System Architecture Diagrams

---

### Document Overview
- **Document Code**: DFAR-DOC-ARCH-04
- **System**: Department of Fisheries and Aquatic Resources Management System (DFAR-MS)
- **Scope**: Structural and physical architecture diagrams, application tiering, component interactions, and deployment topologies.

---

## 1. High-Level Enterprise 3-Tier Architecture

The DFAR system is structured as an enterprise three-tier web application built on the **Yii2 Advanced Framework** architecture, isolating public access, administrative operations, shared business logic, and automated background daemons.

```mermaid
graph TD
    subgraph Client Tier
        PUB_USER["Public Users (Fishermen, Export Companies, Vessel Owners)"]
        OFF_USER["Departmental Staff (FI, AD, DM, DG, Harbour Officers)"]
        EXT_API["External Third Parties & API Consumers"]
    end

    subgraph Web & Presentation Tier (DMZ / Portals)
        FRONTEND["Frontend Public Portal (:8081)<br/>Yii2 Frontend Application<br/>Fisherman Profiles & Self-Service"]
        BACKEND["Backend Admin Management (:8080)<br/>Yii2 Backend Application<br/>Regulatory Operations, Approvals, Dashboards"]
        ROUTER["PHP Built-in / Nginx Reverse Proxy Router<br/>Static Asset Bypass & Error Protection"]
    end

    subgraph Application & Business Logic Tier
        COMMON["Common Shared Library (`/common`)<br/>- ActiveRecord Data Models<br/>- RBAC Auth Middleware<br/>- Validation & Security Enforcers"]
        CONSOLE["Console CLI & Cron Daemons (`/console`)<br/>- `cron.php` Expiration Sweeper<br/>- `BlueTrakerSyncController` (VMS Poller)"]
        SERVICES["Department Service Layer<br/>- CommonService Permission Validator<br/>- LeaveMailer Notification System<br/>- PDF & QR Code Engine (mPDF)"]
    end

    subgraph Data & Storage Tier
        MYSQL[("MySQL 8/9 Enterprise Database<br/>`dfar_ms` (166 Normalized Tables)<br/>InnoDB ACID Storage Engine")]
        UPLOADS[("Protected File System<br/>`/mountpoint/uploads/`<br/>Officer Avatars, Vessel Documents, Inspection Scans")]
    end

    PUB_USER -->|HTTP / HTTPS| ROUTER
    OFF_USER -->|HTTP / HTTPS| ROUTER
    EXT_API -->|REST API Bearer| ROUTER
    ROUTER -->|Port 8081| FRONTEND
    ROUTER -->|Port 8080| BACKEND

    FRONTEND --> COMMON
    BACKEND --> COMMON
    BACKEND --> SERVICES
    CONSOLE --> COMMON

    COMMON --> MYSQL
    SERVICES --> UPLOADS
    CONSOLE --> MYSQL
```

---

## 2. Yii2 Advanced Application Decomposition

```mermaid
graph LR
    subgraph "Workspace Root (`/dfar_ms`)"
        subgraph "frontend/"
            F_CTRL["controllers/"]
            F_MOD["models/"]
            F_VIEW["views/"]
            F_WEB["web/ (index.php, router.php)"]
        end

        subgraph "backend/"
            B_CTRL["controllers/"]
            B_MOD["models/"]
            B_VIEW["views/"]
            B_SERV["services/"]
            B_WEB["web/ (index.php, router.php)"]
        end

        subgraph "common/"
            C_MOD["models/ (ActiveRecord Core)"]
            C_COMP["components/ (WebUser, Helpers)"]
            C_CONF["config/ (main.php, main-local.php)"]
            C_MAIL["mail/ (Trilingual Templates)"]
        end

        subgraph "console/"
            CN_CTRL["controllers/ (BlueTraker, Migrations)"]
            CN_MIG["migrations/ (166 DDL migrations)"]
        end
    end

    F_CTRL --> C_MOD
    B_CTRL --> C_MOD
    B_CTRL --> B_SERV
    CN_CTRL --> C_MOD
```

---

## 3. Request Lifecycle & Security Middleware Pipeline

Every HTTP request entering the DFAR backend traverses a multi-layered security filter pipeline before reaching controller actions:

```mermaid
sequenceDiagram
    autonumber
    actor User as Browser / Client
    participant Router as router.php
    participant Entry as index.php
    participant Yii as yii\web\Application
    participant BaseCtrl as backend\components\Controller
    participant Action as Controller::action()
    participant DB as MySQL `dfar_ms`

    User->>Router: HTTP Request (URL, Headers, Cookies)
    alt Static File (.png, .css, .js)
        Router-->>User: Serve Directly / 404 (Bypass PHP)
    else Missing Source Map (.map)
        Router-->>User: 200 OK (Empty Valid Source Map JSON)
    else Dynamic Route (/boat-transfer/...)
        Router->>Entry: Pass to index.php
        Entry->>Yii: Initialize Application Config & Session
        Yii->>BaseCtrl: beforeAction() Execution
        Note over BaseCtrl: Verify CSRF Token (SameSite Lax, HTTP/HTTPS aware)
        Note over BaseCtrl: Check Guest vs Authenticated Status
        Note over BaseCtrl: Check PUBLIC_ROUTES Whitelist
        alt Unauthorized Guest
            BaseCtrl-->>User: 401 Unauthorized / Redirect to /login
        else Authorized Staff
            BaseCtrl->>Action: Invoke Target Action Method
            Note over Action: Check CommonService::validatePermission('admin')
            Action->>DB: Query / Mutate via ActiveRecord (PDO Prepared)
            DB-->>Action: Return Records / Status
            Action-->>User: Render View (HTML / JSON)
        end
    end
```

---

## 4. Satellite VMS (BlueTraker) Ingress Pipeline

Multi-day vessels transmitting GPS and telemetric reports over satellite relays synchronize with the local database on a continuous schedule:

```mermaid
graph TD
    SAT["Vessel Satellite Transponder<br/>(BlueTraker Maritime Beacon)"]
    GATEWAY["Slovenia / Global BlueTraker API Gateway<br/>(SOAP / REST Web Services)"]
    DAEMON["console/controllers/BlueTrakerSyncController<br/>(Scheduled Daemon Execution)"]
    SYNC_TABLE["`blutracker_api_sync_status`<br/>(Tracks `LastMessageId`)"]
    REPORTS["`bluetraker_reports`<br/>(Normalized Telemetry Store)"]
    DEP_VAL["Departure Clearance Validator<br/>(Checks Speed, Heading, Geofence)"]

    SAT -->|Inmarsat / Iridium Link| GATEWAY
    DAEMON -->|Query High Watermark `LastMessageId`| SYNC_TABLE
    DAEMON -->|HTTP Basic Auth Ingress Request| GATEWAY
    GATEWAY -->>|Batch XML / JSON Telemetry Payload| DAEMON
    DAEMON -->|Bulk INSERT Coordinates & Stats| REPORTS
    DAEMON -->|Update `LastMessageId` Cursor| SYNC_TABLE
    REPORTS --> DEP_VAL
```

---

## 5. Multi-Tier Administrative Leave Approval Chain

The leave management system routes absence applications along strict institutional hierarchies based on workplace assignment and rank:

```mermaid
graph TD
    START([Staff Member Submits Leave Request])
    ACTING[Acting Officer Agrees to Cover Duties]
    CC[Supervising Officer / CC Review]
    AD_ITD{Workplace Location?}
    DG[Director General Final Approval]
    APPROVED([Leave Formally Granted & Logged])

    START --> ACTING
    ACTING --> CC
    CC --> AD_ITD
    AD_ITD -->|District Office Staff| AD[Assistant Director of District]
    AD_ITD -->|Head Office Staff| ITD[IT Director / Head Office Executive]
    AD --> DG
    ITD --> DG
    DG --> APPROVED
```

---

## 6. Fisheries Administrative Hierarchy & Spatial Topology

The territorial administration structure governs how operational jurisdictions flow from Head Office leadership down to individual beach landing points:

```mermaid
graph TD
    HQ["DFAR Central Head Office (Colombo)<br/>Director General (DG) & Directors"]
    
    subgraph "District Tier (16 Coastal Districts)"
        DIST_CBO["m_fi_district: Colombo (CBO)<br/>Assistant Director (AD)"]
        DIST_GLE["m_fi_district: Galle (GLE)<br/>Assistant Director (AD)"]
        DIST_BCO["m_fi_district: Batticaloa (BCO)<br/>Assistant Director (AD)"]
    end
    
    subgraph "Division Tier (FI Divisions)"
        DIV_CBO1["m_division: FI-DIV-CBO-001<br/>Fisheries Inspector (FI)"]
        DIV_CBO2["m_division: FI-DIV-CBO-002<br/>Fisheries Inspector (FI)"]
        DIV_GLE1["m_division: FI-DIV-GLE-001<br/>Fisheries Inspector (FI)"]
        DIV_BCO1["m_division: FI-DIV-BCO-001<br/>Fisheries Inspector (FI)"]
    end
    
    subgraph "Landing Site Tier (Harbours & Anchorages)"
        SITE_H1["m_landing_site: Mutwal Harbour (Harbour)"]
        SITE_L1["m_landing_site: Modara Beach (Land)"]
        SITE_H2["m_landing_site: Galle Port (Harbour)"]
        SITE_L2["m_landing_site: Devinuwara (Land)"]
    end

    HQ --> DIST_CBO
    HQ --> DIST_GLE
    HQ --> DIST_BCO

    DIST_CBO --> DIV_CBO1
    DIST_CBO --> DIV_CBO2
    DIST_GLE --> DIV_GLE1
    DIST_BCO --> DIV_BCO1

    DIV_CBO1 --> SITE_H1
    DIV_CBO1 --> SITE_L1
    DIV_GLE1 --> SITE_H2
    DIV_GLE1 --> SITE_L2
```

---

## 7. Scientific Fisheries Sampling & Research Data Pipeline

The scientific sampling subsystem connects field enumeration events at coastal landing points with national stock assessment models and government export reporting:

```mermaid
graph TD
    ENUM["Field Enumerator / FI<br/>(Conducts on-site survey at Landing Site)"]
    REQ["scientific_enumeration_request<br/>(Validates survey feasibility / weather)"]
    SESS["scientific_data<br/>(Records District, Division, Site, Timestamps)"]
    
    subgraph "Active Fleet & Vessel Sampling"
        FLEET["scientific_fleet_data<br/>(Census of Active Craft by Gear & Type)"]
        VESSEL["scientific_sampling_data<br/>(Individual Sampled Craft Registry)"]
        VOYAGE["scientific_sampling_boat_gear_data<br/>(Trip Duration, Departure Port, Weather, Crew)"]
        GEAR["scientific_sampling_gear_data<br/>(Gear Dimensions, Operations, Fishing Depth)"]
    end
    
    subgraph "Biological & Economic Metrics"
        CATCH["scientific_sampling_catch_data<br/>(Landed Species Weights & State Codes)"]
        LENGTH["scientific_sampling_length_details<br/>(Biometric Morphometrics: Length & Weight)"]
        COST["scientific_sampling_operation_cost<br/>(Fuel, Ice, Bait, Labor Expenses)"]
        UTIL["scientific_sampling_operation_utilization<br/>(Export, Domestic, Dried, Discard Disposition)"]
    end
    
    subgraph "Analytical Reporting & Government Warehousing"
        R_CATCH["scientific_catch_report / report_scientific_catch2"]
        R_LW["scientific_lw_report / report_scientific_lw<br/>(Population Growth Models W=aL^b)"]
        R_UTIL["scientific_util_report<br/>(Post-Harvest Value Realization)"]
        R_EFFORT["report_scientific_craft_gear_wise<br/>(CPUE & Economic Viability Index)"]
    end

    ENUM --> REQ
    REQ -->|Approved Survey| SESS
    SESS --> FLEET
    SESS --> VESSEL
    VESSEL --> VOYAGE
    VOYAGE --> GEAR
    VESSEL --> CATCH
    VESSEL --> LENGTH
    VESSEL --> COST
    COST --> UTIL

    CATCH --> R_CATCH
    LENGTH --> R_LW
    UTIL --> R_UTIL
    GEAR --> R_EFFORT
    COST --> R_EFFORT
```

