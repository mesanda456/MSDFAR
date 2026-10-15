# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Technical Infrastructure Descriptions

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-INFRA-11
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Compute hardware sizing, operating system specifications, PHP runtime tuning, MySQL database server configuration, and network topology dedicated to supporting scientific catch sampling workloads.

---

## 1. Production Compute Infrastructure Specifications

The Scientific Sampling Subsystem requires dedicated compute resources to handle concurrent field data collection by 200+ coastal enumerators alongside complex analytical queries by NARA researchers:

| Specification Tier | Minimum Requirement | Production Recommended | Primary Architectural Rationale |
| :--- | :--- | :--- | :--- |
| **CPU Architecture** | 8 vCPU (x86_64 / ARM64) | 16 vCPU (3.0+ GHz) | High parallel throughput for multi-table statistical aggregations. |
| **RAM (System Memory)**| 16 GB ECC RAM | 32 GB ECC RAM | Dedicated memory for MySQL InnoDB buffer pool (20GB) + PHP processes. |
| **Storage Subsystem** | 250 GB NVMe SSD | 500 GB Enterprise NVMe | Sub-millisecond I/O latency for transactional catch writes and index scans. |
| **Operating System** | Ubuntu 22.04 LTS / RHEL 9 | Ubuntu 24.04 LTS Server | Long-Term Support, Linux kernel 6.x optimized I/O scheduling. |
| **Network Interface** | 1 Gbps Ethernet | 10 Gbps Redundant NIC | Rapid large-scale stock assessment CSV/XLSX streaming. |

---

## 2. PHP 8.x Runtime Configuration (`php.ini`)

The statistical report generation engines (such as length-frequency histograms and monthly catch matrix workbooks) require tailored PHP memory and execution thresholds:

```ini
; /etc/php/8.5/fpm/php.ini (Optimized for Scientific Subsystem)

; Memory threshold for large PhpSpreadsheet workbooks
memory_limit = 512M

; Maximum execution time for long-running IOTC stock exports
max_execution_time = 300

; Input variables limit to support large dynamic catch matrices (50+ fish rows per form)
max_input_vars = 5000

; Maximum upload file size for biometric photo attachments
upload_max_filesize = 32M
post_max_size = 64M

; Timezone synchronization with Sri Lanka Standard Time
date.timezone = "Asia/Colombo"

; OPcache performance optimization
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
```

---

## 3. MySQL 8.0 Server Configuration Tuning (`my.cnf`)

```ini
# /etc/mysql/conf.d/scientific_db.cnf
[mysqld]

# Buffer pool sized to hold the entire scientific database hot indices in RAM
innodb_buffer_pool_size = 20G
innodb_buffer_pool_instances = 8

# Redo log sizing for high-throughput batch ingress
innodb_redo_log_capacity = 2G

# Concurrency & connection handling
max_connections = 300
innodb_thread_concurrency = 16

# Sorting & temporary tables for GROUP BY aggregations
sort_buffer_size = 4M
read_rnd_buffer_size = 8M
tmp_table_size = 64M
max_heap_table_size = 64M

# Character set & collation
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Binary logging for Point-In-Time Recovery
log_bin = /var/log/mysql/mysql-bin.log
expire_logs_days = 14
binlog_format = ROW
```

---

## 4. Network Architecture & Security Port Topologies

```
+---------------------------------------------------------------------------------------------------------+
|                                        NETWORK PORT TOPOLOGY                                            |
+---------------------------------------------------------------------------------------------------------+
| Port  | Protocol | Service                 | Source / Destination               | Security Policy       |
+-------+----------+-------------------------+------------------------------------+-----------------------+
| 443   | HTTPS    | Nginx Reverse Proxy     | Public Internet / SDO Mobile Units | TLS 1.3 Strict HTTPS  |
| 8080  | HTTP     | DFAR Backend Web App    | Internal LAN / Nginx Proxy         | Localhost Binding Only|
| 8082  | HTTP     | Technical Docs Portal   | Departmental Intranet              | Restricted Developer  |
| 3306  | TCP      | MySQL Database          | Application Server Only            | Deny All Public Ingress|
| 22    | SSH      | Administrative Access   | Authorized Bastion Host            | Key-Based MFA Only    |
+---------------------------------------------------------------------------------------------------------+
```
