# Department of Fisheries and Aquatic Resources (DFAR)
## Software Application Documentation

---

### Document Overview
- **Document Code**: DFAR-DOC-APP-05
- **System**: Department of Fisheries and Aquatic Resources Management System (DFAR-MS)
- **Target Audience**: Software Engineers, Application Maintainers, Technical Leads
- **Framework**: Yii Framework 2.0.55 (Advanced Template)
- **Runtime Environment**: PHP 8.2+ / PHP 8.5+

---

## 1. Technology Stack & Dependencies

The DFAR-MS application is built upon a modern, standards-compliant PHP enterprise architecture:

### 1.1 Core Backend Stack
- **Language**: PHP 8.2 / 8.5 (Strict typing, nullsafe operators, optimized JIT engine).
- **Web Framework**: **Yii 2.0.55 Advanced Template** (High performance, component-based MVC architecture with built-in RBAC, CSRF protection, and Active Record ORM).
- **Package Manager**: **Composer 2.10.x** (Locked dependencies in `composer.lock` for reproducible builds).
- **Database Driver**: PHP Data Objects (`pdo_mysql`) with prepared statement security.

### 1.2 Key Libraries & Third-Party Components

| Component / Package | Version | Functional Purpose |
| :--- | :---: | :--- |
| `yiisoft/yii2` | `2.0.55` | Core MVC framework, routing, ActiveRecord ORM, logging |
| `yiisoft/yii2-bootstrap4` | `2.0.12` | Responsive UI components and form controls |
| `mpdf/mpdf` | `8.2.5` | PDF generation for official fishermen ID cards, skipper certificates, and permits |
| `mpdf/qrcode` & `2amigos/qrcode-library`| `^1.2` | Cryptographic QR code generation for document authenticity verification |
| `phpoffice/phpspreadsheet` | `5.0.0` | Excel spreadsheet export/import for district statistical returns |
| `kartik-v/yii2-grid` | `3.5.3` | Advanced enterprise data grid with dynamic sorting, filtering, and export |
| `kartik-v/yii2-widget-select2` | `dev-master`| Dynamic AJAX search dropdowns for large datasets (vessel registries, landing sites) |
| `swiftmailer/swiftmailer` / `symfony/mailer`| `^2.1` | Automated SMTP email delivery for leave notifications and security alerts |

---

## 2. Codebase Organization & Module Structure

The project root is partitioned according to the Yii2 Advanced application standard:

```
MSDFAR/
├── docs/                             # Technical Documentation Suite (11 Standard Documents)
├── run_local.sh                      # Local Development Startup Script (macOS / Linux)
├── stop_local.sh                     # Local Development Stopper Script (macOS / Linux)
├── run_local.bat                     # Local Startup Script (Windows)
├── stop_local.bat                    # Local Stopper Script (Windows)
└── dfar_ms/                          # Main Application Directory
    ├── backend/                      # Administrative Back-Office Application (:8080)
    │   ├── assets/                   # AppAsset bundle declarations
    │   ├── components/               # Base Controller, custom request validators
    │   ├── config/                   # Backend configuration (`main.php`, `params.php`)
    │   ├── controllers/              # 40+ Administrative Controller classes
    │   ├── models/                   # Backend-specific form models and search models
    │   ├── services/                 # CommonService, LeaveMailer, validation services
    │   ├── views/                    # Razor/PHP presentation views and layouts
    │   └── web/                      # Public document root (`index.php`, `router.php`, assets)
    ├── frontend/                     # Public Citizen & Enterprise Portal (:8081)
    │   ├── config/                   # Frontend configuration
    │   ├── controllers/              # Citizen controllers (`SiteController`, `FishermanController`)
    │   ├── views/                    # Public responsive layouts
    │   └── web/                      # Public entry point (`index.php`, `router.php`)
    ├── common/                       # Shared Enterprise Code (Shared across all tiers)
    │   ├── config/                   # Central configurations (`main.php`, `main-local.php`)
    │   ├── models/                   # Core ActiveRecord database entity models (`User`, `LoginForm`)
    │   └── mail/                     # Trilingual email notification templates
    ├── console/                      # CLI Commands & Background Cron Daemons
    │   ├── controllers/              # `BlueTrakerSyncController` (VMS ingress)
    │   └── migrations/               # Database DDL migration history
    └── vendor/                       # Third-party composer packages (Auto-generated)
```

---

## 3. Core Business Controllers & Functional Workflows

### 3.1 Vessel Fleet Operations (`backend/controllers/`)
- [`BoatNumbersController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/BoatNumbersController.php): Master management of boat number registries. Handles boat search by district, status filtering, and initial registration verification.
- [`BoatTransferController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/BoatTransferController.php): Controls the legal transfer of vessel ownership. Includes `actionAddManTransfer()` to log manual off-system transactions with full audit logging.
- [`BoatCancelController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/BoatCancelController.php): Manages decommissioning, loss at sea, and scraping workflows with district office verification.

### 3.2 Licensing & Certifications
- [`NationalLicenseController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/NationalLicenseController.php): Processes coastal EEZ annual licenses. Validates gear parameters and checks vessel sea-worthiness before license issuance.
- [`HighseasLicenseController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/HighseasLicenseController.php): Handles multi-day (IMUL) international water authorizations, validating Indian Ocean Tuna Commission (IOTC) registrations and active VMS serials.
- [`SkipperController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/SkipperController.php): Skipper qualification registry. Coordinates certificate issuance and renewal via `SkipperRenewController.php`.

### 3.3 Harbor Logistics & Clearances
- [`DepartureController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/DepartureController.php): Authorizes vessel departures from fishing harbours. Enforces mandatory pre-departure safety validation:
  1. Valid, unexpired vessel registration.
  2. Certified skipper on board.
  3. Signal ping verification from satellite VMS.
  4. Crew manifest recording.

### 3.4 Statistical Reporting (BSC1 / BSC2)
- [`BscFormsController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/BscFormsController.php): Entry interface for monthly statistical returns submitted by Fisheries Inspectors across all 322 coastal divisions.
- [`BscReportsController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/BscReportsController.php): Aggregates monthly returns into national statistical tables for policy making.

### 3.5 Institutional Governance & HR
- [`LeaveController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/LeaveController.php): Multi-tier staff absence management supporting Acting Officer agreements, Supervising Officer (CC) reviews, Assistant Director endorsements, and Director General approvals.
- [`ErrorController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/ErrorController.php): Centralized exception handler rendering user-friendly trilingual error messages and generating unique audit codes (`ERR-YYYYMMDD-...`).

### 3.6 Fisheries Administrative & Geographic Hierarchy Management
- [`FiDistrictController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/FiDistrictController.php): CRUD and configuration of coastal Fisheries Districts (`m_fi_district`).
- [`DivisionController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/DivisionController.php): Management of Fisheries Inspector Divisions (`m_division`), district parent binding, and active division lists.
- [`LandingSiteController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/LandingSiteController.php): Management of 4,500+ coastal beach landing sites and harbours (`m_landing_site`), division parent linkage, and landing point classifications.
- [`DistrictGearTypesController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/DistrictGearTypesController.php): Controls authorized marine gear classifications permitted per coastal district.

### 3.7 Scientific Catch Sampling & Enumeration Subsystem
- [`ScientificController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/ScientificController.php): Comprehensive field biological sampling session manager. Coordinates vessel sampling profiles, biometric measurements (length, weight), trip costs (fuel, ice, bait, crew), and economic disposition across export, local, dried, and discard markets.
- [`ScientificEnumerationController.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/ScientificEnumerationController.php): Manages field survey authorization requests, observer scheduling, and incident reporting for aborted sampling runs due to weather or zero landings.
- **Key Scientific Models (`dfar_ms/backend/models/`)**:
  - Geographic: [`MFiDistrict.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/MFiDistrict.php), [`MDivision.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/MDivision.php), [`MLandingSite.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/MLandingSite.php)
  - Core Sessions: [`ScientificData.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificData.php), [`ScientificEnumerationRequest.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificEnumerationRequest.php)
  - Vessel & Gear: [`ScientificSamplingData.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificSamplingData.php), [`ScientificSamplingBoatGearData.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificSamplingBoatGearData.php), [`ScientificSamplingGearData.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificSamplingGearData.php)
  - Biology & Economics: [`ScientificSamplingCatchData.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificSamplingCatchData.php), [`ScientificSamplingLengthDetails.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificSamplingLengthDetails.php), [`ScientificSamplingOperationCost.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificSamplingOperationCost.php), [`ScientificSamplingOperationUtilization.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/models/ScientificSamplingOperationUtilization.php)

---

## 4. Key Utility Components

- [`Constant.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/config/Constant.php): Central registry defining user type IDs, operational statuses, number formatting patterns, and institutional workflow stages.
- [`UserTypeUtil.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/config/UserTypeUtil.php): Helper methods to inspect user roles (`hasType()`, `isDG()`, `isDirector()`) and determine dynamic approval jurisdictions based on user assignment and posting.
- [`CommonService.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/services/CommonService.php): Enterprise access controller enforcing explicit RBAC permission checks (`validatePermission($controller, 'admin')`).
