# Department of Fisheries and Aquatic Resources (DFAR)
## Metadata and Data Dictionaries

---

### Document Overview
- **Document Code**: DFAR-DOC-DB-03
- **System**: Department of Fisheries and Aquatic Resources Management System (DFAR-MS)
- **Scope**: Canonical reference for business metadata, system enumerations, number formatting rules, and taxonomical dictionaries.

---

## 1. Business Identifier Formatting Schemas

The DFAR system enforces standardized national numbering formats across all maritime assets, licenses, and official cards:

| Entity Type | Formatting Schema | Regular Expression Pattern | Example |
| :--- | :--- | :--- | :--- |
| **Fisherman ID** | `FM{number}{district_code}` | `^FM\d{3,6}[A-Z]{3}$` | `FM1042COL` |
| **Skipper Card** | `SK{number}{district_code}` | `^SK\d{3,6}[A-Z]{3}$` | `SK0589GAL` |
| **National EEZ License** | `{year}EEZ{boatType}{number}{dist}` | `^\d{4}EEZ[A-Z]{3,5}\d{3,6}[A-Z]{3}$`| `2026EEZOFRP0412COL` |
| **Highseas (IMUL) License**| `{year}HSIMUL{number}{district_code}`| `^\d{4}HSIMUL\d{3,6}[A-Z]{3}$` | `2026HSIMUL0089GAL` |
| **MEA Vessel Number** | `{year}/{boat}{district_code}{id}` | `^\d{4}\/[A-Z0-9-]+\d+$` | `2026/IMUL-COL-441` |
| **System Error Reference** | `ERR-{YYYYMMDD}-{HHMMSS}-{RAND8}` | `^ERR-\d{8}-\d{6}-[A-Z0-9_-]{6,20}$` | `ERR-20261013-103536-AYBBTL_O` |

---

## 2. Core System Enumerations

### 2.1 User Types (`user.type`)
The department operates an organizational hierarchy spanning field officers, regional administrators, and the central executive:

| Type ID | Constant | Title / Organizational Role | Operational Jurisdiction |
| :---: | :--- | :--- | :--- |
| `1` | `Constant::FISHERMAN` | Fisherman / Vessel Owner | Public portal, personal profile & vessels |
| `2` | `Constant::FI` | Fisheries Inspector (FI) | Division level inspection & recommendation |
| `3` | `Constant::AD` | Assistant Director (AD) | District level administrative approval |
| `4` | `Constant::DG` | Director General (DG) | National executive authority |
| `5` | `Constant::DO` | Development Officer (DO) | Statistical enumeration & local support |
| `6` | `Constant::DM` | Director Marine (DM) | Fleet marine operations & resource management |
| `7` | `Constant::ICT_OFFICER` | ICT Technical Officer | IT Division technical operations |
| `8` | `Constant::MEA` | Marine Environment Authority | Coastal conservation coordination |
| `9` | `Constant::DFI` | District Fisheries Inspector | High-tier district oversight |
| `11` | `Constant::ADMIN` | System / IT Administrator | User management, RBAC, error reader, API |
| `15` | `Constant::ITD` | Information Technology Director | Head Office IT management & leave approver |
| `16` | `Constant::ITU` | IT Unit Staff | General IT technical tasks |
| `20` | `Constant::ADMINISTRATION`| Central Administration | Departmental administration & HR |
| `200` | `Constant::EXPORT_COMPANY`| Marine Export Enterprise | Export permit applications & consignment |
| `999` | `Constant::DIRECTOR` | Departmental Director (Legacy) | Division directorates |

---

### 2.2 Regulatory Status Codes (`status`)
Standardized application and workflow states utilized across vessel registrations, licenses, and permits:

| Status Code | Verbal State | Meaning & Business Rule |
| :---: | :--- | :--- |
| `1` | **Pending / Submitted** | Application received; awaiting initial officer review. |
| `10` | **Active** | Standard active status for users, masters, and registries. |
| `200` | **Approved / Completed** | Legally authorized by the final officer in chain (AD or DG). |
| `403` | **Rejected / Revoked** | Application declined, or existing license revoked for violation. |
| `0` | **Deleted / Inactive** | Soft-deleted or decommissioned asset. |
| `PaymentPending` | **Payment Required** | Application accepted; awaiting statutory fee settlement. |

---

### 2.3 Departure Clearance States (`vms_check_status`)
Pre-departure satellite tracking checks for multi-day vessels:

| State Key | Meaning | Clearance Condition |
| :--- | :--- | :--- |
| `Departure Allowed` | Satellite VMS is transmitting valid telemetry within tolerance. | **Approved for Departure** |
| `Violation Detected` | Vessel has an unresolved geofence or maritime boundary violation. | **Prohibited from Departure** |
| `Compulsory Service Pending` | VMS transponder battery or antenna maintenance required. | **Prohibited from Departure** |
| `Other` | Manual inspection required by harbor master. | **On Hold** |

---

## 3. Geographical Master Dictionary

### 3.1 Fisheries Districts (`m_fi_district`)
Sri Lanka's 16 official coastal fisheries administrative districts:

| District ID | District Code | District Name | Headquarter Location |
| :---: | :---: | :--- | :--- |
| `1` | `COL` | Colombo | Colombo Harbor / Head Office |
| `2` | `NEG` | Negombo | Negombo Fisheries Office |
| `3` | `CHI` | Chilaw | Chilaw District Office |
| `4` | `PUT` | Puttalam | Puttalam Office |
| `5` | `KAL` | Kalutara | Kalutara Harbor |
| `6` | `GAL` | Galle | Galle Fishing Port |
| `7` | `MAT` | Matara | Mirissa Fisheries Complex |
| `8` | `TAN` | Tangalle | Kudawella / Tangalle |
| `9` | `HAM` | Hambantota | Hambantota International Harbor |
| `10` | `BAT` | Batticaloa | Batticaloa District Office |
| `11` | `TRI` | Trincomalee | Trincomalee Harbor |
| `12` | `MUL` | Mullaitivu | Mullaitivu Coastal Office |
| `13` | `JAF` | Jaffna | Gurunagar Fisheries Office |
| `14` | `KIL` | Kilinochchi | Kilinochchi Office |
| `15` | `MAN` | Mannar | Mannar District Office |
| `16` | `AMP` | Ampara | Kalmunai / Ampara Office |

---

## 4. Vessel & Craft Taxonomical Metadata

### 4.1 Boat Types (`m_boat_types`)
Classifications determined by propulsion, hull construction, and operational capability:

| Craft Code | Category Name | Typical Hull Length | Operating Range |
| :--- | :--- | :--- | :--- |
| **IMUL** | Inboard Multi-day Vessel | > 32 feet | Highseas / EEZ (10 - 45 days at sea) |
| **IDAY** | Inboard Single-day Vessel | 28 - 32 feet | Coastal EEZ (12 - 24 hours) |
| **OFRP** | Outboard Engine Fiberglass Craft | 18 - 24 feet | Coastal waters (5 - 12 nautical miles) |
| **MTRB** | Motorized Traditional Craft | 15 - 20 feet | Inshore coastal lagoons and reefs |
| **NBSB** | Non-motorized Traditional Craft | 10 - 15 feet | Lagoons, estuaries, shallow bays |
| **NBSV** | Non-motorized Beach Seine Craft | Specialized | Beach seine net deployment (Madel) |

---

## 5. Marine Export Species Dictionary

High-value species subject to mandatory CITES / DFAR quota validations:

| Category Code | Common English Name | Scientific Taxonomy | Quota Tracking Unit |
| :--- | :--- | :--- | :---: |
| **LOB** | Spiny / Rock Lobster | *Panulirus homarus / P. versicolor* | Net Weight (Kg) |
| **BDM** | Sea Cucumber (Beche-de-mer) | *Holothuroidea spp.* | Dry Weight (Kg) |
| **CHK** | Chank Shells | *Turbinella pyrum* | Number of Pieces & Diameter |
| **LF** | Live Ornamental Marine Fish | Multiple Families (*Pomacentridae, etc.*) | Total Piece Count |
| **NAK** | Nakla Marine Molluscs | Standard Marine Mollusca | Gross Weight (Kg) |

---

## 6. Fisheries Geographic Hierarchy Standards

### 6.1 FI Division Identifier Syntax (`m_division`)
Fisheries Inspector Divisions represent operational units supervised by a Fisheries Inspector. Their names follow a standardized district-prefixed naming syntax:
- **Format**: `FI-DIV-{DISTRICT_CODE}-{SERIAL}`
- **Pattern**: `^FI-DIV-[A-Z]{3}-\d{3}$`
- **Examples**:
  - `FI-DIV-MUL-004` (Mullaitivu Division #4)
  - `FI-DIV-TRI-011` (Trincomalee Division #11)
  - `FI-DIV-CBO-002` (Colombo North Division #2)

### 6.2 Landing Site Naming Standards (`m_landing_site`)
Sri Lanka's 4,500+ registered landing sites are categorized by infrastructure type using mandatory classification suffixes in `name`:
- **Harbour Landing Site**: Suffix `(Harbour)` — Deep-water basin with breakwaters and auction halls (e.g. `Devinuwara Fisheres Harbour (Harbour)`, `Beruwala Fisheries Harbour (Harbour)`).
- **Beach / Coastal Anchorage**: Suffix `(Land)` — Open sandy beach or estuarine anchorage for traditional craft and OFRP boats (e.g. `Kaisawella(Land)`, `Dugahawella (Land)`, `Hiriketiya (Land)`).

---

## 7. Scientific Sampling Biological & Economic Dictionaries

### 7.1 Fish Species Taxonomy (`m_fish_types` & `scientific_sampling_catch_data.specie`)
Master biological reference for marine catch sampling:

| Code / ID | Common English Name | Scientific Name | FAO 3-Alpha Code | Commercial Category |
| :---: | :--- | :--- | :---: | :--- |
| `1` | Yellowfin Tuna | *Thunnus albacares* | **YFT** | Large Pelagic / Export Grade |
| `2` | Bigeye Tuna | *Thunnus obesus* | **BET** | Large Pelagic / Export Grade |
| `3` | Skipjack Tuna | *Katsuwonus pelamis* | **SKJ** | Pelagic / Canning & Local |
| `4` | Swordfish | *Xiphias gladius* | **SWO** | Billfish / Export Grade |
| `5` | Sailfish | *Istiophorus platypterus* | **SFA** | Billfish / Local Market |
| `6` | Narrow-barred Spanish Mackerel | *Scomberomorus commerson* | **COM** | Seer Fish / Premium Domestic |
| `7` | Blue Swimming Crab | *Portunus pelagicus* | **PAG** | Crustacean / Processing |
| `8` | Silky Shark | *Carcharhinus falciformis* | **FAL** | Protected / Monitored IOTC |
| `9` | Shortfin Mako | *Isurus oxyrinchus* | **SMA** | Pelagic Shark / Monitored |
| `10` | Trevally / Carangids | *Caranx spp.* | **TRE** | Demersal / Coastal Fresh |

---

### 7.2 Specimen Weight & Processing State Codes (`weight_code`)
Standard conversion factor codes for landed specimen condition:

| Code | State Description | Typical Conversion Factor to Whole Weight |
| :---: | :--- | :---: |
| `1` | **Whole (Round)** — Unaltered specimen | `1.00` |
| `2` | **Gutted** — Viscera removed | `1.15` |
| `3` | **Gilled & Gutted (GG)** — Gills and viscera removed | `1.20` |
| `4` | **Headed & Gutted (HG)** — Head, gills, and viscera removed | `1.45` |
| `5` | **Filleted / Loined** — Skinless/skin-on loins | `2.10` |

---

### 7.3 Biometric Length Measurement Standards (`length_type` & `length_code`)

| `length_type` ID | Metric Name | Anatomical Definition | Applied Species Group |
| :---: | :--- | :--- | :--- |
| `1` | **Total Length (TL)** | From tip of snout to tip of the longer lobe of caudal fin | Teleost, Pelagic, Demersal |
| `2` | **Fork Length (FL)** | From tip of snout to the fork/center of the caudal fin | Tuna, Billfish, Mackerel |
| `3` | **Standard Length (SL)** | From tip of snout to the posterior end of vertebral column | Scientific voucher specimens |
| `4` | **Carapace Width (CW)** | Maximum lateral width of cephalothorax | Crabs (*Portunus pelagicus*) |
| `5` | **Carapace Length (CL)** | Mid-dorsal length from orbital spine to carapace margin | Lobsters (*Panulirus spp.*) |

- **Unit Code (`length_code`)**:
  - `1`: Centimetres (`cm`)
  - `2`: Millimetres (`mm`)

---

### 7.4 Post-Harvest Utilization & Economic Channel Codes (`scientific_sampling_operation_utilization`)

| Channel Field | Target Market | Inspection Requirement | Quality Tier |
| :--- | :--- | :--- | :--- |
| `export_qty` / `export_value` | Air-freight sashimi / EU & US export processors | EU Sanitary Health Certificate + Traceability QR | Premium (A / AA Grade) |
| `local_qty` / `local_value` | Domestic wholesale auction / Central fish markets | Local Municipal Health Clearance | Domestic Fresh (B Grade) |
| `dried_qty` / `dried_value` | Traditional coastal sun-drying & salting yards | DFAR Processor Registration | Processing Grade |
| `discard_qty` / `discard_value` | Non-commercial bycatch thrown overboard | Logged for IOTC discard quota assessment | Unused / Discarded |
| `trash` (`1` / `0`) | Fishmeal plants & fertilizer reduction | Environmental regulatory monitoring | Industrial Reduction |

