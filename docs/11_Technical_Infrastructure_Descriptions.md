# Department of Fisheries and Aquatic Resources (DFAR)
## Technical Infrastructure Descriptions

---

### Document Overview
- **Document Code**: DFAR-DOC-INFRA-11
- **System**: Department of Fisheries and Aquatic Resources Management System (DFAR-MS)
- **Target Audience**: Infrastructure Architects, Network Engineers, Systems Administrators
- **Scope**: Server specifications, network topology, runtime parameters, storage systems, and environment provisioning.

---

## 1. Server Hardware & Operating System Specifications

### 1.1 Production Environment (Recommended Enterprise Benchmark)

| Specification Component | Minimum Benchmark | Recommended Enterprise Tier |
| :--- | :--- | :--- |
| **Operating System** | Ubuntu Server 22.04 LTS / RHEL 9 | Red Hat Enterprise Linux (RHEL) 9 |
| **CPU Architecture** | 4 Cores (x86_64 or ARM64) | 8 Cores (AMD EPYC / Intel Xeon 3.0+ GHz) |
| **System Memory (RAM)** | 16 GB ECC RAM | 32 GB - 64 GB ECC DDR4/DDR5 |
| **Primary Disk Storage** | 200 GB Enterprise NVMe SSD | 500 GB NVMe SSD (RAID-10 Configuration) |
| **Network Interface** | 1 Gbps Full Duplex Ethernet | 10 Gbps Redundant NICs |

### 1.2 Local Development Environment (macOS / Developer Stations)
- **Host OS**: macOS 15+ (Apple Silicon ARM64 / Intel x86_64) or Linux workstation.
- **PHP CLI**: Homebrew PHP 8.2 / 8.5 with `pdo_mysql`, `gd`, `intl`, `mbstring`, `openssl`, `zip`.
- **MySQL Engine**: Local MySQL 8.x / 9.x daemon listening on `127.0.0.1:3306`.
- **Local Application Ports**:
  - `8080`: DFAR Administrative Backend (`http://127.0.0.1:8080`)
  - `8081`: DFAR Citizen Frontend (`http://127.0.0.1:8081`)

---

## 2. Network Topology, Security Zones & Port Allocations

```mermaid
graph TD
    INTERNET((Public Internet))
    FIREWALL[Border Firewall / DDoS Shield]
    LB[Reverse Proxy / SSL Termination<br/>Nginx 1.24+ / HAProxy]

    subgraph DMZ Zone
        WEB_BACKEND["Backend Application Nodes<br/>Private IP: 10.0.10.15<br/>Port 8080 (Admin)"]
        WEB_FRONTEND["Frontend Application Nodes<br/>Private IP: 10.0.10.16<br/>Port 8081 (Public)"]
    end

    subgraph Internal Secure Database Zone
        DB_CLUSTER[("MySQL Database Server<br/>Private IP: 10.0.20.5<br/>Port 3306 (Restricted)")]
        NFS_STORAGE[("Network Attached Storage<br/>`/mountpoint/uploads/`<br/>NFS / Encrypted SAN")]
    end

    INTERNET --> FIREWALL
    FIREWALL -->|Port 443 HTTPS| LB
    LB -->|Internal HTTP Proxy| WEB_FRONTEND
    LB -->|Internal HTTP Proxy| WEB_BACKEND

    WEB_BACKEND -->|Port 3306 (Private Subnet)| DB_CLUSTER
    WEB_FRONTEND -->|Port 3306 (Private Subnet)| DB_CLUSTER
    WEB_BACKEND -->|NFS Mount| NFS_STORAGE
```

### 2.1 Port Allocation Table

| Port | Protocol | Source Zone | Destination Zone | Purpose |
| :---: | :---: | :--- | :--- | :--- |
| **80** | TCP (HTTP) | Public Internet | Load Balancer | Redirect to HTTPS 443 |
| **443** | TCP (HTTPS)| Public Internet | Load Balancer | Secure client web traffic |
| **8080**| TCP (HTTP) | Internal Proxy | Backend Web Server | Administrative application traffic |
| **8081**| TCP (HTTP) | Internal Proxy | Frontend Web Server | Citizen & export company portal traffic |
| **3306**| TCP (MySQL)| Application Subnet | Database Server | Relational database queries |
| **587** | TCP (SMTP) | Web Application | Government Mail Gateway | Outbound TLS email notifications |

---

## 3. Web Server & Reverse Proxy Configuration (Nginx)

In production environments, Nginx acts as the primary reverse proxy, terminating TLS certificates and dispatching requests:

```nginx
# Backend Administrative Host Block
server {
    listen 443 ssl http2;
    server_name msdfar.com admin.msdfar.com;

    ssl_certificate     /etc/ssl/certs/msdfar.crt;
    ssl_certificate_key /etc/ssl/private/msdfar.key;
    ssl_protocols       TLSv1.2 TLSv1.3;

    root /var/www/MSDFAR/dfar_ms/backend/web;
    index index.php;

    # Static Asset Caching
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|svg|woff2?|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        try_files $uri =404;
    }

    # Pass Dynamic Requests to PHP-FPM
    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}
```

---

## 4. PHP Runtime Environment & `php.ini` Directives

To support large document generation (multi-page PDF certificates, high-resolution vessel photos, bulk Excel returns), the PHP runtime must be tuned with the following mandatory directives:

```ini
; Core Memory and Limits
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; Upload and POST Constraints
upload_max_filesize = 64M
post_max_size = 64M
max_file_uploads = 20

; Timezone Standardization
date.timezone = "Asia/Colombo"

; Error Reporting in Production
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Zend OPcache High-Performance Optimization
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
```

---

## 5. Storage Architecture & Mount Point Configuration

1. **Document Storage Path**:
   As configured in [`Constant::$FILE_UPLOAD_PATH`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/config/Constant.php):
   - Production Environment: Dedicated network volume mounted at `../../../../../mountpoint/uploads/`.
   - Local Development Environment: Local directory `dfar_ms/backend/web/uploads/`.
2. **Access Control**:
   The upload directory permissions are restricted to `0755` with ownership assigned to the web daemon user (`www-data` or `staff`), preventing arbitrary script execution within upload directories (`php_flag engine off` in Apache or `location ^~ /uploads/ { try_files $uri =404; }` in Nginx).
