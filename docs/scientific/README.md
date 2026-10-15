# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Technical Documentation Suite

---

### Executive Document Suite Summary
This directory contains the exclusive, standalone technical documentation for the **Scientific Section** of the DFAR Management System (DFAR-MS), incorporating actual database structures, live metrics, and real schema introspection from the MySQL database.

Every topic requested by the Senior Directorate is covered in an individual document:

| # | Topic | Document Title | Document Code | Live DB Coverage |
| :-: | :--- | :--- | :--- | :--- |
| **01** | **Database Architecture** | [Scientific Database Architecture](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/01_Scientific_Database_Architecture.md) | `DFAR-SCI-DOC-DB-01` | InnoDB engine, topology, buffer pool, 112 sessions across 9 districts |
| **02** | **Schemas & Table Descriptions** | [Scientific Schemas & Table Descriptions](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/02_Scientific_Database_Schemas_and_Table_Descriptions.md) | `DFAR-SCI-DOC-DB-02` | All 10 scientific tables + 3 spatial masters with exact column definitions and production row samples |
| **03** | **Metadata & Data Dictionaries** | [Scientific Metadata & Data Dictionaries](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/03_Scientific_Metadata_and_Data_Dictionaries.md) | `DFAR-SCI-DOC-DB-03` | Morphometric units, weight codes, regex formats, and controlled vocabularies |
| **04** | **System Architecture Diagrams** | [Scientific System Architecture Diagrams](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/04_Scientific_System_Architecture_Diagrams.md) | `DFAR-SCI-DOC-ARCH-04` | 3-tier enterprise architecture, complete 10-table Mermaid ERD, and sequence flows |
| **05** | **Software Application Documentation** | [Scientific Software Application Documentation](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/05_Scientific_Software_Application_Documentation.md) | `DFAR-SCI-DOC-APP-05` | `ScientificController`, `ScientificEnumerationController`, `ScientificService`, Active Record models |
| **06** | **Data Flow Diagrams** | [Scientific Data Flow Diagrams](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/06_Scientific_Data_Flow_Diagrams.md) | `DFAR-SCI-DOC-DFD-06` | DFD Level 0 Context, Level 1 Subsystem, and Level 2 Catch/Biometric decomposed flows |
| **07** | **Data-Access Procedures** | [Scientific Data-Access Procedures](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/07_Scientific_Data_Access_Procedures.md) | `DFAR-SCI-DOC-DAP-07` | Eager loading with(), ACID transaction persistence, row locking `FOR UPDATE`, cursor batching |
| **08** | **User Permission & Access-Control** | [Scientific User Permission & Access Control](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/08_Scientific_User_Permission_and_Access_Control.md) | `DFAR-SCI-DOC-SEC-08` | RBAC matrix (SDO, FI, AD, NARA), territorial district/division row-level scoping (RLS) |
| **09** | **System Integration Documentation** | [Scientific System Integration Documentation](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/09_Scientific_System_Integration_Documentation.md) | `DFAR-SCI-DOC-INT-09` | NARA research REST API, IOTC Form 1BC/1RC treaty compliance, GIS grid bindings |
| **10** | **Backup, Recovery & Maintenance** | [Scientific Backup, Recovery & Maintenance](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/10_Scientific_Backup_Recovery_and_Maintenance.md) | `DFAR-SCI-DOC-OPS-10` | Automated single-transaction backup script, Point-in-Time Recovery (PITR), index maintenance |
| **11** | **Technical Infrastructure Descriptions**| [Scientific Technical Infrastructure Descriptions](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/scientific/11_Scientific_Technical_Infrastructure_Descriptions.md)| `DFAR-SCI-DOC-INFRA-11`| Server compute sizing, PHP 8.5 `php.ini` memory thresholds, MySQL buffer pool tuning |

---

### Real Production Database Summary
- **Master Sessions**: 112 active enumeration sessions logged in `scientific_data`.
- **Daily Feasibility Requests**: 73 records in `scientific_enumeration_request`.
- **Total Fleet Census**: 145 records counting 4,433 active craft across landing sites.
- **Sampled Vessels**: 120 craft sampling profiles (45 unique registered boats).
- **Landed Catch Logs**: 194 species catch records totaling **120,032.50 kg** of fish landed across 22 species.
- **Specimen Biometrics**: 73 measured individual fish (lengths: 3cm to 555cm, average 96.75cm).
- **Voyage Economics**: 61 trip expenditure sheets (fuel, ice, bait, labour, food).
- **Post-Harvest Utilization**: 56 disposition records (Export: 10,208.4 kg, Local: 11,124.4 kg, Dried: 8,782.5 kg, Discards: 5,187.5 kg).
