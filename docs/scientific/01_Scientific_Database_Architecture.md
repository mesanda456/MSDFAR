# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Database Architecture Documentation

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-DB-01
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Classification**: Departmental Technical Architecture Specification
- **Target Audience**: Technical Directorate, Database Administrators (DBAs), Systems Architects, Data Analysts, and NARA Marine Researchers
- **Scope**: Exclusive architectural documentation of the **Scientific section** of the DFAR-MS database, including storage engines, buffer pool tuning, table clustering, indexing strategies, and live production database metrics.

---

## 1. High-Level DBMS Architecture & Topology

The Scientific Sampling Subsystem is built on **MySQL 8.0+ / MariaDB 10.6+** utilizing the high-integrity **InnoDB Storage Engine**. The subsystem records empirical catch data, fishing efforts, vessel biometrics, and post-harvest economics across coastal landing sites in Sri Lanka.

```
+----------------------------------------------------------------------------------------------------+
|                           DFAR SCIENTIFIC SAMPLING DATABASE SUBSYSTEM                              |
|                              Database Name: dfar_ms | Engine: InnoDB                               |
|                     Character Set: utf8mb4 | Collation: utf8mb4_unicode_ci                         |
+----------------------------------------------------------------------------------------------------+
                                                  |
           +--------------------------------------+-------------------------------------+
           |                                                                            |
           v                                                                            v
+-------------------------------------+                      +---------------------------------------+
|    SPATIAL ADMINISTRATIVE HIERARCHY |                      |     FIELD ENUMERATION FEASIBILITY     |
|    - m_fi_district (16 rows)        |                      |     - scientific_enumeration_request  |
|    - m_division (322 rows)          |                      |       (73 logged requests)            |
|    - m_landing_site (4,559 rows)    |                      +---------------------------------------+
+-------------------------------------+                                         |
           |                                                                    v
           +----------------------------------------------------> +-----------------------------------+
                                                                  |  LANDING SITE SESSION HEADER      |
                                                                  |  - scientific_data (112 sessions) |
                                                                  +-----------------------------------+
                                                                                |
                                     +------------------------------------------+---------------------+
                                     |                                                                |
                                     v                                                                v
                  +--------------------------------------+                 +-----------------------------------------+
                  |  FLEET ACTIVITY CENSUS               |                 |  SAMPLED VESSEL PROFILING               |
                  |  - scientific_fleet_data             |                 |  - scientific_sampling_data             |
                  |    (145 records, 4,433 boats counted)|                 |    (120 boat samples, 45 unique vessels)|
                  +--------------------------------------+                 +-----------------------------------------+
                                                                                                |
                                +---------------------------------------------------------------+---------------------+
                                |                                                               |                     |
                                v                                                               v                     v
+-----------------------------------------------+ +-----------------------------------------------+ +------------------------------------+
| VOYAGE & EFFORT PROFILES                      | | LANDED CATCH COMPOSITION                      | | VOYAGE ECONOMICS                   |
| - scientific_sampling_boat_gear_data (99 rows)| | - scientific_sampling_catch_data (194 rows,   | | - scientific_sampling_operation_   |
| - scientific_sampling_gear_data (100 rows,    | |   120,032.50 kg landed catch)                 | |   cost (61 cost sheets)            |
|   17 distinct gear types)                     | +-----------------------------------------------+ +------------------------------------+
+-----------------------------------------------+                       |                                             |
                                                                        v                                             v
                                                +-----------------------------------------------+ +------------------------------------+
                                                | BIOMETRIC MORPHOMETRICS                       | | COMMERCIAL DISPOSITION             |
                                                | - scientific_sampling_length_details (73 rows,| | - scientific_sampling_operation_   |
                                                |   min: 3cm, max: 555cm, avg: 96.75cm)         | |   utilization (56 records)         |
                                                +-----------------------------------------------+ +------------------------------------+
```

---

## 2. Live Database Metrics (Scientific Section Only)

Extracted directly from the production `dfar_ms` schema:

| Table Name | Storage Engine | Live Row Count | Primary Key | Key Relational Foreign Key | Functional Domain |
| :--- | :--- | :---: | :--- | :--- | :--- |
| `scientific_enumeration_request` | InnoDB | **73** | `id` (INT) | `user`, `district`, `division`, `landing_site` | Enumerator daily feasibility & obstruction reasons |
| `scientific_data` | InnoDB | **112** | `id` (INT) | `district`, `division`, `landing_site`, `added_by` | Master landing site sampling session header |
| `scientific_fleet_data` | InnoDB | **145** | `id` (INT) | `scientific_id` -> `scientific_data.id` | Census of total operating boats by craft & gear |
| `scientific_sampling_data` | InnoDB | **120** | `id` (INT) | `scientific_id` -> `scientific_data.id` | Sampled boat identification & registration plate |
| `scientific_sampling_boat_gear_data` | InnoDB | **99** | `id` (INT) | `sampling_data_id` -> `scientific_sampling_data.id` | Nautical voyage, engine HP, departure/arrival tracking |
| `scientific_sampling_gear_data` | InnoDB | **100** | `id` (INT) | `boat_data_id` -> `boat_gear_data.id` | Technical gear metrics (depth, soaking hours, hooks) |
| `scientific_sampling_catch_data` | InnoDB | **194** | `id` (INT) | `craft_id` -> `scientific_sampling_data.id` | Disaggregated catch weight (120,032.5 kg logged) |
| `scientific_sampling_length_details` | InnoDB | **73** | `id` (INT) | `sampling_data_id` -> `scientific_sampling_data.id` | Biological length frequency (FL/TL biometrics) |
| `scientific_sampling_operation_cost` | InnoDB | **61** | `id` (INT) | `sampling_data_id` -> `scientific_sampling_data.id` | Voyage expenditures (fuel, ice, bait, food, labour) |
| `scientific_sampling_operation_utilization`| InnoDB | **56** | `id` (INT) | `operation_cost_id` -> `operation_cost.id` | Commercial disposal (export, local, dried, discard) |

---

## 3. Storage Configuration & ACID Characteristics

1. **Transaction Isolation**: Configured at `READ COMMITTED` to guarantee non-blocking analytical reads while enumerators submit catch sheets.
2. **Clustered Indexes**: All 10 tables utilize synthetic `AUTO_INCREMENT INT` primary keys (`PRIMARY`), organizing data on-disk in B+ Trees ordered by monotonic insertion sequence.
3. **Foreign Key Integrity**: Relational constraints are configured with `ON UPDATE CASCADE ON DELETE RESTRICT` to prevent accidental deletion of historical catch logs.
4. **Buffer Pool Sizing**: For the Scientific analytical reporting workloads, `innodb_buffer_pool_size` is allocated at 70% of server RAM to ensure multi-year catch queries reside entirely in cache.
5. **Character Collation**: `utf8mb4_unicode_ci` provides full Unicode support for landing site vernacular names, Tamil and Sinhala character descriptions in `reson` and `remark` fields.

---

## 4. Geographic Distribution of Active Scientific Sessions

The live `scientific_data` sessions are distributed across the following administrative coastal fisheries districts:

| District Code | Coastal District Name | Active Sampling Sessions | Percentage of Total |
| :---: | :--- | :---: | :---: |
| `BCO` | **Batticaloa** | 45 sessions | 40.2% |
| `CBO` | **Colombo** | 44 sessions | 39.3% |
| `MAT` | **Matara** | 7 sessions | 6.3% |
| `GAL` | **Galle** | 6 sessions | 5.4% |
| `NEG` | **Negombo** | 4 sessions | 3.6% |
| `CHW` | **Chilaw** | 2 sessions | 1.8% |
| `TRN` | **Trincomalee** | 2 sessions | 1.8% |
| `PUT` | **Puttalam** | 1 session | 0.9% |
| `TAN` | **Tangalle** | 1 session | 0.9% |
| **Total** | **National Baseline** | **112 Sessions** | **100.0%** |
