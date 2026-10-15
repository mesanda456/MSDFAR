# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Backup, Recovery, and Maintenance Procedures

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-OPS-10
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Disaster recovery runbooks, non-blocking logical backup scripts, Point-In-Time Recovery (PITR), index maintenance, and long-term archival policies dedicated to scientific tables.

---

## 1. Scientific Subsystem Backup Strategy

Because scientific catch records serve as permanent baseline data for national fisheries quota negotiations and international treaty compliance, scientific data is **never purged** and requires rigorous multi-tiered backup schedules:

| Backup Tier | Target Data | Frequency | Method / Tool | Retention Policy | Storage Destination |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Full Scientific Dump** | All 10 Scientific Tables + Geographic Masters | Daily at 02:00 LST | `mysqldump --single-transaction` | 90 Days Daily, 7 Years Monthly | Dedicated NAS / Offsite S3 Bucket |
| **Continuous Binlogs** | Binary Transaction Logs (`mysql-bin.000*`) | Real-time Streaming | MySQL Binlog Replication | 14 Days Continuous | Replicated DB Standby Node |
| **Weekly Analytical Snapshot** | Aggregated Catch & Length Tables | Sunday at 04:00 LST | Compressed Zstandard (`zstd`) | Permanent Historical Archive | Cold Archival Storage |

---

## 2. Automated Non-Blocking Backup Script

```bash
#!/usr/bin/env bash
# /opt/dfar/scripts/backup_scientific_subsystem.sh
set -euo pipefail

BACKUP_DIR="/var/backups/dfar/scientific"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/scientific_tables_${TIMESTAMP}.sql.gz"

mkdir -p "$BACKUP_DIR"

echo "[$(date)] Starting non-blocking backup of DFAR Scientific Subsystem..."

# Export exclusively the 10 scientific tables + spatial masters
mysqldump \
  --single-transaction \
  --quick \
  --skip-lock-tables \
  --default-character-set=utf8mb4 \
  --triggers \
  --routines \
  dfar_ms \
  scientific_enumeration_request \
  scientific_data \
  scientific_fleet_data \
  scientific_sampling_data \
  scientific_sampling_boat_gear_data \
  scientific_sampling_gear_data \
  scientific_sampling_catch_data \
  scientific_sampling_length_details \
  scientific_sampling_operation_cost \
  scientific_sampling_operation_utilization \
  m_fi_district \
  m_division \
  m_landing_site \
  | gzip -9 > "$BACKUP_FILE"

echo "[$(date)] Backup completed successfully: ${BACKUP_FILE} ($(du -h "$BACKUP_FILE" | cut -f1))"
```

---

## 3. Disaster Recovery & Restoration Runbook

In the event of accidental data corruption or hardware failure:

### Step 1: Isolate the Scientific Subsystem
Put the web application into maintenance mode to prevent incoming enumerator writes:
```bash
touch /Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/runtime/.maintenance
```

### Step 2: Restore Logical Snapshot
```bash
gunzip < /var/backups/dfar/scientific/scientific_tables_20261015_020000.sql.gz | mysql -u root -p dfar_ms
```

### Step 3: Roll Forward via Binary Logs (PITR)
Replay transactions up to the exact timestamp prior to the incident:
```bash
mysqlbinlog \
  --start-datetime="2026-10-15 02:00:00" \
  --stop-datetime="2026-10-15 09:45:00" \
  --database=dfar_ms \
  /var/log/mysql/mysql-bin.000142 | mysql -u root -p dfar_ms
```

### Step 4: Remove Maintenance Lock
```bash
rm -f /Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/runtime/.maintenance
```

---

## 4. Periodic Database Table Maintenance

To maintain high query performance for stock aggregation reports:
```sql
-- Monthly Index Cardinality Re-evaluation
ANALYZE TABLE scientific_data;
ANALYZE TABLE scientific_sampling_catch_data;
ANALYZE TABLE scientific_sampling_length_details;
ANALYZE TABLE scientific_fleet_data;

-- Quarterly Table Defragmentation
OPTIMIZE TABLE scientific_sampling_catch_data;
OPTIMIZE TABLE scientific_sampling_length_details;
```
