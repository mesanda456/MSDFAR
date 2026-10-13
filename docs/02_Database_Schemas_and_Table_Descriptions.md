# Department of Fisheries and Aquatic Resources (DFAR)
## Database Schemas and Table Descriptions

---

### Document Overview
- **Document Code**: DFAR-DOC-DB-02
- **System**: Department of Fisheries and Aquatic Resources Management System (DFAR-MS)
- **Scope**: Detailed schema descriptions, column definitions, constraints, and relationships for core production tables.

---

## 1. Authentication & Identity Management Cluster

### 1.1 `user` Table
Stores user accounts for officers, fishermen, yard operators, and export companies.

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Unique system user ID |
| `nic` | `varchar(255)` | NO | NULL | **UNIQUE** | National Identity Card / Username |
| `auth_key` | `varchar(32)` | NO | NULL | | Cookie authentication hash |
| `password_hash` | `varchar(255)` | NO | NULL | | Bcrypt password hash |
| `password_reset_token` | `varchar(255)` | YES | NULL | **UNIQUE** | Temporary password reset token |
| `reset_token_expires_at`| `datetime` | YES | NULL | | Expiration timestamp for reset token |
| `email` | `varchar(255)` | NO | NULL | **UNIQUE** | Official or personal email address |
| `status` | `smallint` | NO | 10 | | Account status (10=Active, 9=Inactive, 0=Deleted) |
| `created_at` | `int` | NO | NULL | | Epoch timestamp of account creation |
| `updated_at` | `int` | NO | NULL | | Epoch timestamp of last modification |
| `verification_token` | `varchar(255)` | YES | NULL | | Email verification token |
| `profile_id` | `int` | NO | 0 | | Pointer to `profile_officer` or `profile_fisherman` |
| `type` | `varchar(20)` | NO | 0 | | User Type Code (e.g. `1`=Fisherman, `2`=FI, `3`=AD, `4`=DG, `11`=Admin) |
| `user_permission` | `int` | NO | 1 | | Permission level flag |
| `failed_login_attempts` | `int` | NO | 0 | | Counter for failed logins (lockout trigger) |
| `lockout_count` | `int` | NO | 0 | | Progressive lockout tier counter |
| `locked_until` | `int` | YES | NULL | | Epoch timestamp when lockout expires |
| `last_failed_login_at` | `int` | YES | NULL | | Epoch timestamp of latest login failure |

---

### 1.2 `profile_officer` Table
Stores administrative and personnel information for DFAR department officers.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Unique officer profile identifier |
| `first_name` | `varchar(200)` | NO | Officer first name |
| `last_name` | `varchar(200)` | NO | Officer last name |
| `nic` | `varchar(20)` | YES | Officer National Identity Card number |
| `current_designation`| `varchar(100)` | YES | Title (e.g. Director General, Assistant Director, FI) |
| `current_workplace` | `varchar(100)` | YES | Station (Head Office, Colombo District Office, etc.) |
| `district` | `int` | YES (FK) | Reference to `m_fi_district.id` |
| `division` | `int` | YES (FK) | Reference to `m_division.id` |
| `harbour` | `varchar(100)` | YES | Assigned fishing harbour |
| `profile_image` | `varchar(100)` | YES | Avatar filename (default: `avatar-1.png`) |
| `user_level` | `varchar(50)` | NO | Clearance level (e.g. `Black`, `Gold`) |
| `status` | `int` | NO | Officer status flag |

---

## 2. Vessel & Boat Registration Cluster

### 2.1 `boat_numbers` Table
Master ledger of authorized fishing vessel registrations.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Unique boat number record identifier |
| `boat_number` | `varchar(100)` | NO | Official vessel registration code |
| `boat_category` | `int` | NO (FK) | References `m_boat_category.id` |
| `boat_type` | `int` | NO (FK) | References `m_boat_types.id` |
| `fisheries_district` | `int` | NO (FK) | District where craft is registered |
| `fisheries_division` | `int` | NO (FK) | Division where craft is stationed |
| `status` | `int` | NO | Registry status (1=Pending, 200=Approved, 403=Rejected) |
| `approval_stage` | `varchar(100)` | NO | Current officer in approval chain |
| `created` | `datetime` | NO | Date and time when registration was created |
| `approved_time` | `datetime` | YES | Timestamp of final DG/AD approval |

---

### 2.2 `boat_number_transfer_request` Table
Records vessel ownership transfers between fishermen or legal entities.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Unique transfer request ID |
| `boat_number_id` | `int` | NO (FK) | Pointer to `boat_numbers.id` |
| `current_owner_id` | `int` | NO (FK) | Pointer to seller in `profile_fisherman.id` |
| `new_owner_id` | `int` | NO (FK) | Pointer to buyer in `profile_fisherman.id` |
| `transfer_date` | `datetime` | NO | Date when sale or transfer became legally effective |
| `approval_stage` | `varchar(100)` | NO | Approval chain stage |
| `status` | `int` | NO | Status code (1=Submitted, 200=Completed) |

---

### 2.3 `boat_number_cancel_requests` Table
Handles vessel de-registration, scrap, destruction, or export disposal.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Cancellation request identifier |
| `boat_number_id` | `int` | NO (FK) | Vessel identifier in `boat_numbers.id` |
| `repairable` | `int` | NO | Flag indicating if craft is salvageable |
| `proposed_dispose` | `varchar(500)` | YES | Destruction / dismantling location and method |
| `approval_stage` | `varchar(100)` | NO | Approval workflow status |
| `status` | `int` | NO | Cancellation approval status |

---

## 3. Licensing & Certifications Cluster

### 3.1 `national_license` Table
Authorizes commercial fishing within Sri Lanka's Exclusive Economic Zone (EEZ).

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Unique national license record |
| `license_number` | `varchar(100)` | NO | National License ID (`{year}EEZ...`) |
| `boat_id` | `int` | NO (FK) | Associated registered vessel |
| `fisherman_id` | `int` | NO (FK) | Licensee (`profile_fisherman.id`) |
| `valid_from` | `date` | NO | License validity commencement date |
| `valid_to` | `date` | NO | License expiration date |
| `gear_type` | `int` | NO (FK) | Authorized fishing gear classification |
| `fee` | `decimal(10,2)`| NO | Statutory fee charged |
| `status` | `int` | NO | 1=Applied, 200=Issued, 403=Revoked/Expired |

---

### 3.2 `highseas_license` Table
Authorizes multi-day vessels (IMUL) to operate in international waters (Highseas) compliant with IOTC (Indian Ocean Tuna Commission) conventions.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Highseas permit identifier |
| `license_number` | `varchar(100)` | NO | Official Highseas code (`{year}HSIMUL...`) |
| `boat_id` | `int` | NO (FK) | Pointer to `boat_numbers.id` |
| `vms_device_serial`| `varchar(100)` | NO | Active satellite VMS transponder serial |
| `iotc_number` | `varchar(100)` | YES | Registered Indian Ocean Tuna Commission ID |
| `valid_from` | `date` | NO | Start date |
| `valid_to` | `date` | NO | End date |
| `approval_stage` | `varchar(100)` | NO | Approval chain |
| `status` | `int` | NO | Regulatory status |

---

### 3.3 `skipper` Table
Professional skipper qualification and licensing registry.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Skipper record ID |
| `skipper_number` | `varchar(100)` | NO | National Skipper ID (`SK...`) |
| `nic` | `varchar(20)` | NO | Skipper NIC number |
| `full_name` | `varchar(255)` | NO | Full legal name |
| `experience_years` | `int` | YES | Years of sea navigation experience |
| `training_status` | `varchar(100)` | YES | Marine training certification level |
| `valid_to` | `date` | YES | Certificate validity date |
| `status` | `int` | NO | Active credential status |

---

## 4. Departure & Harbor Logistics Cluster

### 4.1 `departure_requests` Table
Records applications by vessel operators to depart from fishing harbours.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Departure clearance request ID |
| `boat_id` | `int` | NO (FK) | Craft preparing for sea voyage |
| `skipper_id` | `int` | NO (FK) | Assigned licensed skipper |
| `harbour_id` | `int` | NO (FK) | Departure fishing harbour |
| `intended_return` | `date` | YES | Expected voyage duration / return date |
| `vms_check_status` | `varchar(50)` | NO | Satellite signal active check (Cleared/Pending) |
| `departure_allowed`| `tinyint(1)` | NO | Harbor master authorization (1=Allowed, 0=Denied) |
| `created_at` | `datetime` | NO | Departure timestamp |

---

## 5. Satellite VMS & BlueTraker Cluster

### 5.1 `bluetraker_reports` Table
Stores raw and normalized satellite telemetry received from vessel BlueTraker beacons.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Ingress report ID |
| `MessageId` | `bigint` | NO | Satellite gateway unique message identifier |
| `DeviceId` | `varchar(100)` | NO | Hardware device identifier |
| `BoatId` | `int` | YES (FK) | Mapped registered vessel |
| `Latitude` | `decimal(10,7)`| NO | Geographic latitude |
| `Longitude` | `decimal(10,7)`| NO | Geographic longitude |
| `Speed` | `decimal(5,2)` | YES | Vessel speed in knots |
| `Heading` | `decimal(5,2)` | YES | Vessel compass direction (0-360) |
| `BatteryVoltage` | `decimal(4,2)` | YES | Transponder battery voltage |
| `Timestamp` | `datetime` | NO | GPS signal acquisition timestamp |

---

## 6. BSC Monthly Statistical Return Cluster (Recent Migrations)

### 6.1 `bsc1_submissions` Table
Monthly district statistical submission for coastal crafts and local operations.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Unique submission ID |
| `fi_division_id` | `int` | NO (FK) | Fisheries Division responsible |
| `period_date` | `date` | NO | Reporting month (e.g. `2026-09-01`) |
| `beach_seines` | `int` | NO | Active beach seine operations count |
| `status` | `varchar(50)` | NO | Status (`Draft`, `Submitted`, `Approved`) |
| `approval_stage` | `varchar(50)` | NO | Approval tier (`FI`, `AD`, `Completed`) |
| `created_at` | `datetime` | NO | Submission timestamp |

**Unique Constraint**: `uq_bsc1_division_period` on `(fi_division_id, period_date)` enforces strictly one return per division monthly.

---

### 6.2 `submission_audit_log` Table
Immutable chronological ledger tracking modifications, resubmissions, and review actions on statistical returns.

| Column | Data Type | Nullable | Description |
| :--- | :--- | :---: | :--- |
| `id` | `int` | NO (PK) | Audit entry ID |
| `form_type` | `varchar(10)` | NO | Return type (`BSC1` or `BSC2`) |
| `submission_id` | `int` | NO | Foreign reference to submission |
| `action` | `varchar(100)`| NO | Action (`SUBMIT`, `AD_APPROVE`, `REJECT`) |
| `performed_by` | `int` | NO (FK) | User ID who executed the action |
| `comments` | `text` | YES | Officer evaluation remarks |
| `created_at` | `datetime` | NO | Action timestamp |

---

## 7. Fisheries Administrative & Geographic Hierarchy Cluster

### 7.1 `m_fi_district` Table
Master table of the coastal Fisheries Inspectorate Districts across Sri Lanka (e.g., Colombo, Galle, Chilaw, Batticaloa, Jaffna, Trincomalee). Represents the top administrative boundary in fisheries management.

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Unique FI District ID |
| `name` | `varchar(200)` | NO | NULL | | Official Fisheries District Name (e.g. `Colombo`, `Galle`, `Batticaloa`) |
| `status` | `int` | NO | NULL | | Record Status (1=Active, 0=Inactive) |
| `code` | `varchar(255)` | YES | NULL | | Standard District Code (e.g. `CBO`, `GLE`, `CHW`, `BCO`, `JFN`) |

---

### 7.2 `m_division` Table
Master table of Fisheries Inspector Divisions (FI Divisions). Each division belongs strictly to a parent FI District and represents the operational zone of a designated Fisheries Inspector (FI).

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Unique FI Division ID |
| `district_id` | `int` | NO | NULL | **FOREIGN KEY** | References parent `m_fi_district.id` (Index: `fk_disreict`) |
| `name` | `varchar(200)` | NO | NULL | | FI Division Name / Identifier code (e.g. `FI-DIV-MUL-004`, `FI-DIV-TRI-011`) |
| `status` | `int` | NO | NULL | | Record Status (1=Active, 0=Inactive) |

---

### 7.3 `m_landing_site` Table
Master ledger of authorized fishing landing sites, anchorages, and coastal beach landing points where fishing craft berth, unload catches, or undergo biological sampling.

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Unique Landing Site ID |
| `division_id` | `int` | NO | NULL | **FOREIGN KEY** | References parent `m_division.id` (Index: `landing`) |
| `name` | `varchar(200)` | NO | NULL | | Official landing site name (e.g. `Devinuwara Fisheres Harbour (Harbour)`, `Kaisawella(Land)`) |
| `status` | `int` | NO | NULL | | Record Status (1=Active, 0=Inactive) |

---

### 7.4 Hierarchy Relationship Architecture
```
m_fi_district (District Level: CBO, GLE, BCO)
       │
       ▼  (1 : N via district_id)
m_division (FI Division Level: FI-DIV-*)
       │
       ▼  (1 : N via division_id)
m_landing_site (Landing Site Level: Harbour / Beach Landing Site)
```
Every vessel registry, catch log, scientific field sampling event, and BSC monthly return binds directly to this tri-level hierarchy for spatial and administrative normalization.

---

## 8. Scientific Fisheries Sampling & Research Subsystem (Core 10 Tables & Analytical Views)

> [!IMPORTANT]
> ### Senior Technical Directorate Specification: Core Scientific Tables
> The core scientific sampling subsystem is governed by the following 10 specialized database tables according to the official technical specification:
> 
> 1. **`scientific_enumeration_request`** — *Asking whether the enumerator can carry out the enumeration or not* `(id, user, request_date, date, can_continue, reason, status, district, division, landing_site)`
> 2. **`scientific_data`** — *Details of the completed enumeration* `(id, district, division, landing_site, start_time, end_time, added_by, status, approval_stage, inspected_by)`
> 3. **`scientific_fleet_data`** — *Summary of the boats operated in the landing site* `(id, boat_type, sub_category, gear_type, no_of_boats, status)`
> 4. **`scientific_sampling_boat_gear_data`** — *Details of the sampling boat* `(id, sampling_data_id, fishey_type, sub_category, engine_hp, departure_date, departure_time, departure_district, departure_division, depature_port, weather, arrival_date, remark, crew_members_count, unloading_type, gear_setting_time, days, hours, status)`
> 5. **`scientific_sampling_catch_data`** — *Total weight of the catch by the particular sampling boat* `(id, craft_id, specie, gear, weight_code, weight)`
> 6. **`scientific_sampling_data`** — *Relevant scientific record submitted from this boat* `(id, scientific_id, boat_number, status)`
> 7. **`scientific_sampling_gear_data`** — *Details of the gear used in the particular sampling boat* `(id, boat_data_id, gear, target_species, operations_per_trip, fishing_time_days, fishing_time_hours, fishing_depth, g_code, extra, type, status)`
> 8. **`scientific_sampling_length_details`** — *Details of the specie sampled* `(id, sampling_data_id, gear, specie, specie_count, weight, weight_code, length_type, length_code, length, status)`
> 9. **`scientific_sampling_operation_cost`** — *Expenditure data of the boat owner* `(id, fuel_qty, fuel_price, ice_qty, ice_price, bait_qty, bait_price, labour_cost, food_water, other, remark, sampling_data_id)`
> 10. **`scientific_sampling_operation_utilization`** — *Details on how the harvest is utilized, discarded and trashed* `(id, operation_cost_id, specie, export_qty, export_value, local_qty, local_value, dried_qty, dried_value, discard_qty, discard_value, trash, status)`

---

### 8.1 Detailed Table Schemas (Core 10 Tables)

#### 8.1.1 `scientific_enumeration_request` Table
**Functional Purpose**: *Asking whether the enumerator can carry out the enumeration or not.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Enumeration request record ID |
| `user` | `int` | NO | NULL | **FOREIGN KEY** | Field enumerator user ID |
| `request_date` | `date` | YES | NULL | | Date when enumeration was planned |
| `date` | `datetime` | NO | NULL | | Timestamp of field survey event |
| `can_continue` | `varchar(100)` | NO | NULL | | Boolean flag (`yes` / `no`) whether sampling was possible |
| `reson` / `reason` | `varchar(200)` | YES | NULL | | Reason if sampling aborted (e.g. `Bad weather`, `No fleet landings`) |
| `status` | `int` | NO | NULL | | Administrative status code |
| `district` | `int` | NO | NULL | **FOREIGN KEY** | Target `m_fi_district.id` |
| `division` | `int` | NO | NULL | **FOREIGN KEY** | Target `m_division.id` |
| `landing_site` | `int` | NO | NULL | **FOREIGN KEY** | Target `m_landing_site.id` |

---

#### 8.1.2 `scientific_data` Table
**Functional Purpose**: *Details of the completed enumeration.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Unique scientific sampling session ID |
| `district` | `int` | NO | NULL | **FOREIGN KEY** | References `m_fi_district.id` |
| `division` | `int` | NO | NULL | **FOREIGN KEY** | References `m_division.id` |
| `landing_site` | `int` | NO | NULL | **FOREIGN KEY** | References `m_landing_site.id` |
| `start_time` | `datetime` | NO | NULL | | Session survey start timestamp |
| `end_time` | `datetime` | NO | NULL | | Session survey conclusion timestamp |
| `added_by` | `int` | NO | NULL | **FOREIGN KEY** | Officer ID who recorded the session (`user.id`) |
| `status` | `int` | NO | NULL | | Workflow status flag |
| `approval_stage` | `varchar(50)` | NO | 'Pending' | | Approval status (`Pending`, `Approved`, `Rejected`) |
| `inspected_by` | `int` | YES | NULL | **FOREIGN KEY** | Supervising scientific officer or AD ID |

---

#### 8.1.3 `scientific_fleet_data` Table
**Functional Purpose**: *Summary of the boats operated in the landing site.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Fleet observation ID |
| `scientific_id` | `int` | NO | NULL | **FOREIGN KEY** | References parent `scientific_data.id` |
| `boat_type` | `int` | NO | NULL | **FOREIGN KEY** | Boat classification ID (`m_boat_types.id`) |
| `sub_category` | `int` | NO | NULL | **FOREIGN KEY** | Boat subcategory ID (`m_boat_sub_category.id`) |
| `gear_type` | `int` | NO | NULL | **FOREIGN KEY** | Marine gear classification (`m_gear_types.id`) |
| `no_of_boats` | `int` | NO | NULL | | Total boat count operated in this category |
| `status` | `int` | NO | 1 | | Active status code |

---

#### 8.1.4 `scientific_sampling_boat_gear_data` Table
**Functional Purpose**: *Details of the sampling boat.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Boat voyage sampling ID |
| `sampling_data_id` | `int` | NO | NULL | **FOREIGN KEY** | References `scientific_sampling_data.id` |
| `fishey_type` | `int` | NO | NULL | **FOREIGN KEY** | Fishery classification ID |
| `sub_category` | `int` | YES | NULL | **FOREIGN KEY** | Boat subcategory ID |
| `engine_hp` | `varchar(11)` | NO | NULL | | Propulsion engine horsepower rating |
| `departure_date` | `date` | NO | NULL | | Date vessel departed for sea |
| `departure_time` | `time` | NO | NULL | | Time of sea departure |
| `departure_district`| `int` | NO | NULL | **FOREIGN KEY** | Departure `m_fi_district.id` |
| `departure_division`| `int` | NO | NULL | **FOREIGN KEY** | Departure `m_division.id` |
| `depature_port` | `int` | NO | NULL | **FOREIGN KEY** | Departure `m_landing_site.id` |
| `weather` | `varchar(200)` | NO | NULL | | Marine weather / oceanographic conditions |
| `arrival_date` | `date` | NO | NULL | | Date vessel returned to landing site |
| `remark` | `varchar(200)` | YES | NULL | | Field officer observations |
| `crew_members_count`| `int` | NO | NULL | | Total crew personnel on board |
| `unloading_type` | `varchar(200)` | NO | NULL | | Catch handling mode (Manual, Crane, Chilled box) |
| `gear_setting_time`| `varchar(100)` | NO | NULL | | Duration taken to deploy fishing gear |
| `days` | `int` | NO | NULL | | Voyage duration in days |
| `hours` | `int` | NO | NULL | | Voyage duration residual hours |
| `status` | `int` | NO | 1 | | Profile status flag |

---

#### 8.1.5 `scientific_sampling_catch_data` Table
**Functional Purpose**: *Total weight of the catch by the particular sampling boat.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Catch measurement ID |
| `craft_id` | `int` | NO | NULL | **FOREIGN KEY** | Pointer to sampled craft / voyage record |
| `specie` | `int` | NO | NULL | **FOREIGN KEY** | Fish species ID (`m_fish_types.id`) |
| `gear` | `int` | NO | NULL | **FOREIGN KEY** | Gear with which specimen was captured |
| `weight_code` | `int` | NO | NULL | | Weight condition code (1=Whole, 2=Gutted, 3=Gilled/Gutted) |
| `weight` | `double` | NO | NULL | | Total catch weight landed by this boat in kilograms (kg) |

---

#### 8.1.6 `scientific_sampling_data` Table
**Functional Purpose**: *Relevant scientific record submitted from this boat.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Sampled craft record ID |
| `scientific_id` | `int` | NO | NULL | **FOREIGN KEY** | References parent session `scientific_data.id` |
| `boat_number` | `varchar(200)` | NO | NULL | | Registration number of sampled boat |
| `status` | `int` | NO | NULL | | Sampling record status flag |

---

#### 8.1.7 `scientific_sampling_gear_data` Table
**Functional Purpose**: *Details of the gear used in the particular sampling boat.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Gear deployment record ID |
| `boat_data_id` | `int` | NO | NULL | **FOREIGN KEY** | References `scientific_sampling_boat_gear_data.id` |
| `gear` | `int` | NO | NULL | **FOREIGN KEY** | Gear type ID (`m_gear_types.id`) |
| `target_species` | `int` | NO | NULL | **FOREIGN KEY** | Target commercial species (`m_fish_types.id`) |
| `operations_per_trip`| `int` | NO | NULL | | Number of sets/shots carried out |
| `fishing_time_days` | `double` | NO | NULL | | Active gear soaking duration in days |
| `fishing_time_hours`| `double` | NO | NULL | | Active gear soaking duration in hours |
| `fishing_depth` | `double` | NO | NULL | | Depth of fishing grounds (meters/fathoms) |
| `g_code` | `int` | NO | NULL | | Standard statistical gear code |
| `extra` | `varchar(500)` | YES | NULL | | Technical parameters (mesh size, hook size, line length) |
| `type` | `varchar(100)` | NO | NULL | | Gear classification (`Primary`, `Secondary`) |
| `status` | `int` | NO | NULL | | Record status code |

---

#### 8.1.8 `scientific_sampling_length_details` Table
**Functional Purpose**: *Details of the specie sampled.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Biometric specimen ID |
| `sampling_data_id` | `int` | NO | NULL | **FOREIGN KEY** | References `scientific_sampling_data.id` |
| `gear` | `int` | NO | NULL | **FOREIGN KEY** | Gear deployed (`m_gear_types.id`) |
| `specie` | `int` | NO | NULL | **FOREIGN KEY** | Species ID (`m_fish_types.id`) |
| `specie_count` | `int` | NO | NULL | | Number of specimens sampled in this length class |
| `weight` | `double` | NO | NULL | | Specimen sample weight (kg) |
| `weight_code` | `int` | NO | NULL | | Weight state code |
| `length_type` | `int` | NO | NULL | | Length measurement type (1=Total Length TL, 2=Fork Length FL, 3=Standard SL) |
| `length_code` | `int` | NO | NULL | | Unit code (1=cm, 2=mm) |
| `length` | `double` | NO | NULL | | Measured specimen length value |
| `status` | `int` | NO | NULL | | Measurement validation status |

---

#### 8.1.9 `scientific_sampling_operation_cost` Table
**Functional Purpose**: *Expenditure data of the boat owner.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Operational cost ledger ID |
| `fuel_qty` | `double` | YES | NULL | | Fuel consumed (litres) |
| `fuel_price` | `double` | YES | NULL | | Total expenditure on fuel (LKR) |
| `ice_qty` | `double` | YES | NULL | | Chilling ice quantity (blocks/kg) |
| `ice_price` | `double` | YES | NULL | | Total expenditure on ice (LKR) |
| `bait_qty` | `double` | YES | NULL | | Bait quantity used (kg) |
| `bait_price` | `double` | YES | NULL | | Expenditure on bait (LKR) |
| `labour_cost` | `double` | YES | NULL | | Crew wages and share distributions (LKR) |
| `food_water` | `double` | YES | NULL | | Rations and drinking water provisions (LKR) |
| `other` | `double` | YES | NULL | | Miscellaneous voyage costs (LKR) |
| `remark` | `varchar(500)` | YES | NULL | | Cost evaluation remarks |
| `sampling_data_id` | `int` | NO | NULL | **FOREIGN KEY** | References `scientific_sampling_data.id` |

---

#### 8.1.10 `scientific_sampling_operation_utilization` Table
**Functional Purpose**: *Details on how the harvest is utilized, discarded and trashed.*

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int` | NO | Auto Inc | **PRIMARY KEY** | Utilization ledger ID |
| `operation_cost_id`| `int` | NO | NULL | **FOREIGN KEY** | References parent sampling / cost session |
| `specie` | `int` | NO | NULL | **FOREIGN KEY** | Fish species ID (`m_fish_types.id`) |
| `export_qty` | `double` | YES | NULL | | Catch volume sold for export (kg) |
| `export_value` | `double` | YES | NULL | | Export sales revenue (LKR) |
| `local_qty` | `double` | YES | NULL | | Catch volume sold at domestic landing auctions (kg) |
| `local_value` | `double` | YES | NULL | | Local sales revenue (LKR) |
| `dried_qty` | `double` | YES | NULL | | Catch volume converted into dry fish (kg) |
| `dried_value` | `double` | YES | NULL | | Dry fish market value (LKR) |
| `discard_qty` | `double` | YES | NULL | | Non-commercial catch discarded at sea (kg) |
| `discard_value` | `double` | YES | NULL | | Estimated economic loss (LKR) |
| `trash` | `int` | NO | 0 | | Trash fish / fishmeal reduction flag (1=Yes, 0=No) |
| `status` | `int` | YES | NULL | | Record status |

---

### 8.4 Analytical Reporting Views & Aggregate Tables

These 8 tables provide high-performance denormalized analytical views and aggregate caches utilized by statistical dashboards, export generation, and stock research reporting.

#### 8.4.1 `scientific_catch_report` Table
Denormalized flat table aggregating session metadata (district, division, landing site, officer), sampled boat, species, gear, weight, and description for instant reporting queries.

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `id`, `district_name`, `district`, `division_name`, `division`, `landing_site`, `start_time`, `added_by`, `boat_number`, `name`, `weight`, `specie`, `weight_code`, `gear`, `code`, `description` | `varchar` / `int` / `double` | Denormalized join of `scientific_data`, `scientific_sampling_data`, `scientific_sampling_catch_data`, and master taxonomy tables. |

#### 8.4.2 `scientific_lw_report` Table
Denormalized length-weight view linking spatial landing origins with specimen lengths and sample weights for biological curve calculations ($W = aL^b$).

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `id`, `district_name`, `district`, `division_name`, `division`, `landing_site`, `start_time`, `added_by`, `boat_number`, `name`, `length`, `weight` | `varchar` / `int` / `double` | Specimen level length-weight pairing with spatial coordinates. |

#### 8.4.3 `scientific_util_report` Table
Summary view of post-harvest commercial channel disposition across export, local, dried, and discard markets by boat and species.

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `scientifi_data_id`, `start_time`, `added_by`, `boat_number`, `name`, `id`, `operation_cost_id`, `specie`, `export_qty`, `export_value`, `local_qty`, `local_value`, `dried_qty`, `dried_value`, `discard_qty`, `discard_value`, `trash`, `status` | `varchar` / `int` / `double` | Full revenue and quantity breakdown by utilization channel. |

#### 8.4.4 `report_scientific_catch2` Table
Extended catch report integrating multi-stage voyage financials with catch weights and species volumes.

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `scientifi_data_id`, `sampling_id`, `boat_number`, `district_name`, `district`, `division_name`, `division`, `landing_site`, `start_time`, `added_by`, `name`, `weight`, `specie_name`, `gear`, `weight_code`, `code`, `description`, `export_qty`, `export_value`, `local_qty`, `local_value`, `dried_qty`, `dried_value`, `discard_qty`, `discard_value`, `trash`, `status` | `varchar` / `int` / `double` | Composite catch and economic realization report across sampling tiers. |

#### 8.4.5 `report_scientific_craft` Table
Analytical view reconciling sampled field vessels with official registry data in `boat_numbers` (owner name, owner NIC, yard number, vessel length, registration date).

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `boat_registration_number`, `boat_owner_name`, `nic`, `address`, `owner_contact_details`, `name`, `yard_number`, `length`, `registration_number_issue_date` | `varchar` / `double` / `datetime` | Regulatory validation matching field sampled vessels with official naval registry entries. |

#### 8.4.6 `report_scientific_craft_gear_wise` Table
Comprehensive multidimensional data cube aggregating vessel trip metrics, gear types, soaking durations, fishing depths, fuel/ice economics, and crew complements.

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `scientifi_data_id`, `district`, `sampling_id`, `boat_number`, `gear`, `type`, `gear_code`, `gear_name`, `target_species`, `target_species_name`, `operations_per_trip`, `fishing_time_days`, `fishing_time_hours`, `fishing_depth`, `g_code`, `extra`, `fishey_type`, `sub_category`, `engine_hp`, `departure_date`, `departure_time`, `departure_district`, `departure_division`, `depature_port`, `depature_port_name`, `weather`, `arrival_date`, `crew_members_count`, `unloading_type`, `days`, `hours`, `fuel_qty`, `fuel_price`, `ice_qty`, `ice_price`, `bait_qty`, `bait_price`, `labour_cost`, `food_water`, `other` | `varchar` / `int` / `double` / `date` | Definitive CPUE (Catch Per Unit Effort) and bio-economic assessment view. |

#### 8.4.7 `report_scientific_fleet` Table
Fleet census view cross-tabulating active vessels by boat type, sub-category, gear type, and coastal district.

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `scientifi_data_id`, `district`, `start_time`, `added_by`, `boat_type`, `boat_type_code`, `sub_category`, `sub_category_code`, `gear_type`, `code`, `gear_description`, `no_of_boats` | `varchar` / `int` / `datetime` | Statistical distribution of active fleet strength by marine zone. |

#### 8.4.8 `report_scientific_lw` Table
Aggregate length-frequency view by gear and species across sampling sessions for regional fishery management.

| Key Columns | Type | Description |
| :--- | :--- | :--- |
| `scientifi_data_id`, `district`, `start_time`, `boat_number`, `name`, `id`, `sampling_data_id`, `gear`, `specie`, `specie_count`, `weight`, `weight_code`, `length_type`, `length_code`, `length`, `status`, `code`, `description` | `varchar` / `int` / `double` / `datetime` | Length-frequency distribution table for marine biologists. |

