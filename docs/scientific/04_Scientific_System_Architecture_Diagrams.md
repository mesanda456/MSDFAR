# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: System Architecture Diagrams

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-ARCH-04
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Comprehensive architectural, topological, relational, and component diagrams exclusively representing the Scientific section of the DFAR Management System.

---

## 1. 3-Tier Subsystem Enterprise Architecture

The Scientific Sampling Subsystem is organized in three decoupled tiers:

```mermaid
graph TD
    subgraph Presentation_Tier [Tier 1: Presentation & Field Capture]
        SDO_UI[Field Enumerator Mobile / Web Portal<br/>SDO Prototype / scientificEnumeration]
        FI_UI[Fisheries Inspector Verification Interface<br/>fiScientific.php / fiScientificview.php]
        NARA_UI[NARA Research & Analytics Portal<br/>naraScientific.php / scientific1-4 Reports]
    end

    subgraph Application_Tier [Tier 2: Business Logic & Processing]
        SC_CTRL[ScientificController<br/>backend/controllers/ScientificController.php]
        SE_CTRL[ScientificEnumerationController<br/>backend/controllers/ScientificEnumerationController.php]
        SVC[ScientificService<br/>backend/services/ScientificService.php]
        MODELS[ActiveRecord Domain Models<br/>ScientificData, ScientificSamplingCatchData, etc.]
    end

    subgraph Data_Tier [Tier 3: Database & Storage Engine]
        DB[(MySQL 8.0 / MariaDB<br/>InnoDB Buffer Pool 70% RAM)]
        SPATIAL_TBLS[Spatial Tables<br/>m_fi_district, m_division, m_landing_site]
        SCI_TBLS[10 Core Scientific Tables<br/>Sessions, Fleet, Catches, Biometrics, Economics]
    end

    SDO_UI -->|HTTP POST JSON Payload| SE_CTRL
    FI_UI -->|Approval & Inspection RPC| SC_CTRL
    NARA_UI -->|Statistical Query Requests| SC_CTRL

    SC_CTRL --> SVC
    SE_CTRL --> SVC
    SVC --> MODELS
    MODELS -->|PDO Prepared Queries| DB

    DB --> SPATIAL_TBLS
    DB --> SCI_TBLS
```

---

## 2. Complete Scientific Relational Entity-Relationship Diagram (ERD)

The diagram below reflects the exact foreign key linkages and physical structure of the production database:

```mermaid
erDiagram
    m_fi_district ||--o{ m_division : "contains"
    m_division ||--o{ m_landing_site : "administers"

    m_fi_district ||--o{ scientific_enumeration_request : "target district"
    m_division ||--o{ scientific_enumeration_request : "target division"
    m_landing_site ||--o{ scientific_enumeration_request : "target site"

    m_fi_district ||--o{ scientific_data : "session district"
    m_division ||--o{ scientific_data : "session division"
    m_landing_site ||--o{ scientific_data : "session site"

    scientific_data ||--o{ scientific_fleet_data : "scientific_id"
    scientific_data ||--o{ scientific_sampling_data : "scientific_id"

    scientific_sampling_data ||--o{ scientific_sampling_boat_gear_data : "sampling_data_id"
    scientific_sampling_boat_gear_data ||--o{ scientific_sampling_gear_data : "boat_data_id"

    scientific_sampling_data ||--o{ scientific_sampling_catch_data : "craft_id"
    scientific_sampling_data ||--o{ scientific_sampling_length_details : "sampling_data_id"

    scientific_sampling_data ||--o{ scientific_sampling_operation_cost : "sampling_data_id"
    scientific_sampling_operation_cost ||--o{ scientific_sampling_operation_utilization : "operation_cost_id"

    scientific_enumeration_request {
        int id PK
        int user FK
        date request_date
        datetime date
        varchar can_continue
        varchar reson
        int status
        int district FK
        int division FK
        int landing_site FK
    }

    scientific_data {
        int id PK
        int district FK
        int division FK
        int landing_site FK
        datetime start_time
        datetime end_time
        int added_by FK
        int status
        varchar approval_stage
        int inspected_by FK
    }

    scientific_fleet_data {
        int id PK
        int scientific_id FK
        int boat_type FK
        int sub_category FK
        int gear_type FK
        int no_of_boats
        int status
    }

    scientific_sampling_data {
        int id PK
        int scientific_id FK
        varchar boat_number
        int status
    }

    scientific_sampling_boat_gear_data {
        int id PK
        int sampling_data_id FK
        int fishey_type FK
        int sub_category FK
        varchar engine_hp
        date departure_date
        time departure_time
        int departure_district FK
        int departure_division FK
        int depature_port FK
        varchar weather
        date arrival_date
        int crew_members_count
        varchar unloading_type
        varchar gear_setting_time
        int days
        int hours
        varchar remark
        int status
    }

    scientific_sampling_gear_data {
        int id PK
        int boat_data_id FK
        int gear FK
        int target_species FK
        int operations_per_trip
        double fishing_time_days
        double fishing_time_hours
        double fishing_depth
        int g_code
        varchar extra
        varchar type
        int status
    }

    scientific_sampling_catch_data {
        int id PK
        int craft_id FK
        int specie FK
        int gear FK
        int weight_code
        double weight
    }

    scientific_sampling_length_details {
        int id PK
        int sampling_data_id FK
        int gear FK
        int specie FK
        int specie_count
        double length
        int length_type
        int length_code
        double weight
        int weight_code
        int status
    }

    scientific_sampling_operation_cost {
        int id PK
        int sampling_data_id FK
        double fuel_qty
        double fuel_price
        double ice_qty
        double ice_price
        double bait_qty
        double bait_price
        double labour_cost
        double food_water
        double other
        varchar remark
    }

    scientific_sampling_operation_utilization {
        int id PK
        int operation_cost_id FK
        int specie FK
        double export_qty
        double export_value
        double local_qty
        double local_value
        double dried_qty
        double dried_value
        double discard_qty
        double discard_value
        int trash
        int status
    }
```

---

## 3. Scientific Subsystem Security & Data Flow Pipeline

```mermaid
sequenceDiagram
    autonumber
    actor SDO as Statistical Enumerator (SDO)
    participant APP as ScientificEnumerationController
    participant SVC as ScientificService
    participant DB as MySQL InnoDB dfar_ms
    actor FI as Fisheries Inspector (FI)
    actor NARA as NARA Research Scientist

    SDO->>APP: Submit Daily Request (REQ-20231107-204-55)
    APP->>DB: INSERT into scientific_enumeration_request (can_continue='yes')
    SDO->>APP: Submit Completed Session (SCI-20230602-1-2)
    APP->>SVC: validateAndPersistSamplingPayload()
    SVC->>DB: BEGIN TRANSACTION
    SVC->>DB: INSERT scientific_data (status=1, stage='Pending')
    SVC->>DB: INSERT scientific_fleet_data (boats=100)
    SVC->>DB: INSERT scientific_sampling_data (boat='IMULA333BCO')
    SVC->>DB: INSERT scientific_sampling_catch_data (weight=10.0kg)
    SVC->>DB: INSERT scientific_sampling_length_details (length=7.0cm)
    SVC->>DB: INSERT scientific_sampling_operation_cost & utilization
    SVC->>DB: COMMIT TRANSACTION
    DB-->>APP: Success Session ID #2

    FI->>APP: Review Field Session #2
    APP->>DB: UPDATE scientific_data SET approval_stage='Approved', inspected_by=FI_ID
    DB-->>FI: Status Confirmed (stage='Approved')

    NARA->>APP: Request Catch & Biometric Extract
    APP->>DB: SELECT SUM(weight), AVG(length) FROM scientific_sampling_catch_data
    DB-->>NARA: Aggregated Stock Assessment Dataset
```
