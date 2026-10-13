# Department of Fisheries and Aquatic Resources (DFAR)
# Technical Documentation Suite (DFAR-MS)

---

### Master Index & Documentation Hub
This directory contains the official, comprehensive technical documentation for the **Department of Fisheries and Aquatic Resources Management System (DFAR-MS)**, fulfilling all institutional requirements set forth by senior technical leadership and government audit standards.

Each document is a standalone, exhaustive specification covering its respective domain in complete detail.

---

## Documentation Index

| # | Document Code | Document Title | Description | Link |
| :-: | :--- | :--- | :--- | :--- |
| **MASTER** | `DFAR-DOC-DB-MASTER` | **Comprehensive Database Master Documentation** | Unified 4-pillar specification requested by Senior Directorate: (1) Structures & Schemas, (2) Data Dictionaries & Metadata, (3) Data Workflow Descriptions, and (4) Data-Access Procedures. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/00_Comprehensive_Database_Documentation.md) |
| **01** | `DFAR-DOC-DB-01` | **Database Architecture Documentation** | High-level DBMS topology, InnoDB ACID compliance, 11 functional clusters across 166 tables, sizing, indexing, and connection pooling. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/01_Database_Architecture.md) |
| **02** | `DFAR-DOC-DB-02` | **Database Schemas & Table Descriptions** | Comprehensive schema definitions, column specs, constraints, keys, and foreign relationships for production tables, including the **FI Geographic Hierarchy (`m_fi_district`, `m_division`, `m_landing_site`)** and **all 18 Scientific Catch Sampling tables**. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/02_Database_Schemas_and_Table_Descriptions.md) |
| **03** | `DFAR-DOC-DB-03` | **Metadata & Data Dictionaries** | Canonical data dictionary, identifier schemas (`FM...`, `SK...`, `EEZ...`), User Type IDs, status codes, FI division syntax, landing site classifications (`(Harbour)` vs `(Land)`), and marine species taxonomies. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/03_Metadata_and_Data_Dictionaries.md) |
| **04** | `DFAR-DOC-ARCH-04` | **System Architecture Diagrams** | Visual architectural specifications with Mermaid diagrams: 3-tier enterprise structure, Yii2 Advanced layout, security pipeline, VMS, leave routing, **FI Administrative Hierarchy topology**, and **Scientific Sampling Data Pipeline**. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/04_System_Architecture_Diagrams.md) |
| **05** | `DFAR-DOC-APP-05` | **Software Application Documentation** | Application stack breakdown (PHP 8.2/8.5, Yii 2.0.55), directory structure, core controllers (including `FiDistrictController`, `DivisionController`, `LandingSiteController`, `ScientificController`), services, and utility components. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/05_Software_Application_Documentation.md) |
| **06** | `DFAR-DOC-DFD-06` | **Data Flow Diagrams (DFD)** | Level 0 Context Diagram, Level 1 Subsystem flows, and Level 2 process flows for vessel registry, harbour departures, BSC statistical returns, and **Scientific Field Sampling & Enumeration**. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/06_Data_Flow_Diagrams.md) |
| **07** | `DFAR-DOC-DAP-07` | **Data-Access Procedures** | ActiveRecord patterns, query optimization, N+1 query prevention, parameterized statements, transaction isolation, and direct DB access policies. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/07_Data_Access_Procedures.md) |
| **08** | `DFAR-DOC-SEC-08` | **User Permission & Access-Control Procedures** | Bcrypt authentication, progressive lockout protection, Cloudflare Turnstile, Yii2 Database RBAC (`auth_assignment`), and public route whitelisting. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/08_User_Permission_and_Access_Control_Procedures.md) |
| **09** | `DFAR-DOC-INT-09` | **System Integration Documentation** | BlueTraker satellite VMS ingress, departmental SMTP gateway (`mail.fisheriesdept.gov.lk`), REST APIs (`/api/v1`), and QR code verification. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/09_System_Integration_Documentation.md) |
| **10** | `DFAR-DOC-OPS-10` | **Backup, Recovery & Maintenance Procedures** | Non-blocking daily MySQL backups, point-in-time recovery (PITR) via binlogs, disaster recovery runbooks, automated crons, and table optimizations. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/10_Backup_Recovery_and_Maintenance_Procedures.md) |
| **11** | `DFAR-DOC-INFRA-11` | **Technical Infrastructure Descriptions** | Hardware benchmarks, network topologies, DMZ & firewall port tables, Nginx reverse proxy configs, `php.ini` runtime tuning, and storage mount points. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/11_Technical_Infrastructure_Descriptions.md) |
| **12** | `DFAR-DOC-SCI-12` | **Scientific Sampling Specification** | Official 10-table specification provided by Technical Directorate: feasibility evaluation, completed enumeration, fleet census, boat profiles, landed catch weights, gear parameters, specimen biometrics, boat owner costs, and post-harvest disposition. | [View Document](file:///Users/mesandasethumika/Desktop/MSDFAR/docs/12_Scientific_Sampling_Specification.md) |

---

## System Quick Reference

- **Repository Root**: `/Users/mesandasethumika/Desktop/MSDFAR`
- **Application Directory**: `/Users/mesandasethumika/Desktop/MSDFAR/dfar_ms`
- **Administrative Backend**: [http://localhost:8080](http://localhost:8080)
- **Citizen / Public Frontend**: [http://localhost:8081](http://localhost:8081)
- **Database Engine**: MySQL 8.x / 9.x (`dfar_ms`)
