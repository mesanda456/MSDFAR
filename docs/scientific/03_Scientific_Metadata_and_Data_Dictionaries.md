# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Metadata and Data Dictionaries

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-DB-03
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Comprehensive metadata definitions, physical and logical domain taxonomies, standardized units of measure, regular expression validation rules, and controlled system enumerations governing scientific sampling data.

---

## 1. Metadata Governance Standards

The Scientific Sampling Subsystem adheres to **ISO/IEC 11179 Metadata Registry Standards** and **FAO / IOTC Fisheries Data Specifications**:
1. **Semantic Attribute Naming**: Direct semantic correspondence between database attribute names and fisheries terminology (e.g., `fishing_time_hours`, `operations_per_trip`, `total_weight_kg`, `length_type`).
2. **Explicit Unit Declarations**: Every numerical biometric and operational parameter maps to a mandatory physical unit of measure.
3. **Controlled Enumeration Domain**: Categorical attributes are bound to normalized foreign key lookup master tables (`m_fish_types`, `m_gear_types`, `m_boat_types`, `m_boat_sub_category`, `m_fi_district`, `m_division`, `m_landing_site`).

---

## 2. Standardized Units of Measure

| Metric Domain | Target Entity | Physical Unit | Precision / Datatype | Database Field | Example Real Data |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Landed Catch Weight** | Fish Specimen Weight | Kilograms (`kg`) | `DOUBLE` / `DECIMAL(10,2)` | `catch_data.weight` | `10.00 kg`, `250.50 kg` |
| **Specimen Biometrics** | Fish Morphometrics | Centimeters (`cm`) | `DOUBLE` / `DECIMAL(6,2)` | `length_details.length` | `7.00 cm`, `96.75 cm` |
| **Specimen Body Mass** | Individual Fish Mass | Grams (`g`) | `DOUBLE` / `DECIMAL(8,2)` | `length_details.weight` | `4.00 g`, `1,250.00 g` |
| **Engine Propulsion** | Vessel Inboard/Outboard | Horsepower (`HP`) | `VARCHAR(11)` | `boat_gear_data.engine_hp` | `22`, `40`, `115` |
| **Fishing Depth** | Gear Immersion Depth | Meters (`m`) | `DOUBLE` | `gear_data.fishing_depth` | `3.0 m`, `45.0 m` |
| **Voyage Duration** | Days at Sea | Days (`d`) | `INT` | `boat_gear_data.days` | `3 days`, `14 days` |
| **Soaking / Tow Time** | Active Gear Immersion | Hours (`h`) | `DOUBLE` | `gear_data.fishing_time_hours` | `3.0 hrs`, `8.5 hrs` |
| **Operational Currency** | Voyage Costs & Sales | Sri Lankan Rupees (`LKR`) | `DOUBLE` / `DECIMAL(12,2)` | `operation_cost.fuel_price` | `LKR 340.00 / L` |
| **Fuel Consumed** | Diesel / Kerosene | Liters (`L`) | `DOUBLE` | `operation_cost.fuel_qty` | `324.0 L` |
| **Ice Consumed** | Crushed Ice Preservation | Blocks / kg | `DOUBLE` | `operation_cost.ice_qty` | `342 blocks` |

---

## 3. Canonical Business Identifier Schemas

All field-generated sampling documents, requests, and asset tags follow strict regular expression validation rules:

| Scientific Identifier | Structural Format | Regular Expression Pattern | Production Example |
| :--- | :--- | :--- | :--- |
| **Sampling Session Code** | `SCI-{YYYYMMDD}-{SITE_ID}-{ID}` | `^SCI-\d{8}-\d{1,5}-\d+$` | `SCI-20230602-1-2` |
| **Enumeration Request** | `REQ-{YYYYMMDD}-{USER}-{ID}` | `^REQ-\d{8}-\d{1,5}-\d+$` | `REQ-20231107-204-55` |
| **Vessel Registration Plate**| `{CATEGORY}{REG_NUM}{DIST_CODE}` | `^[A-Z0-9-]{6,20}$` | `IMULA333BCO` |
| **District Code** | `{3-LETTER-ALPHA}` | `^[A-Z]{3}$` | `BCO` (Batticaloa), `CBO` (Colombo) |

---

## 4. Controlled Vocabularies & System Dictionaries

### 4.1 Enumeration Feasibility Codes (`scientific_enumeration_request.can_continue`)
Determines whether daily sampling can proceed at the landing site:
- `yes` - Sampling conditions optimal; fleet landed and sampling commenced.
- `no` - Sampling obstructed. Mandatory entry required in `reson` column (e.g. Cyclone warning, communal dispute, religious festival, zero vessels landed).

### 4.2 Multi-Tier Approval States (`scientific_data.approval_stage`)
Tracks verification through the fisheries chain of command:
- `Pending` (109 active sessions) - Field session submitted by Statistical Enumerator; awaiting verification.
- `Approved` (2 active sessions) - Inspected and formally endorsed by territorial Fisheries Inspector (`inspected_by`).
- `Rejected` (1 active session) - Data returned to field officer due to biometric outlier or validation failure.

### 4.3 Morphometric Length Measurement Types (`length_details.length_type`)
- `1` - **Fork Length (FL)**: Tip of the snout to the center fork of the caudal fin (Tuna, Mackerel, Carangids).
- `2` - **Total Length (TL)**: Tip of the snout to the extreme tip of the compressed caudal fin (Sharks, Rays, Demersal finfish).
- `3` - **Standard Length (SL)**: Tip of the snout to the end of the caudal peduncle (Vertebral column terminus).

### 4.4 Weight Measurement Codes (`weight_code`)
- `1` - Standard Net Kilograms (`kg`).
- `2` - Metric Tonnes (`MT`).
- `3` - Standard Inspected Fish Crates / Baskets (Volumetric raising factor applied).

### 4.5 Weather Observation Classifications (`boat_gear_data.weather`)
- `calm` - Sea state 0-2 (Beaufort scale); wind < 10 knots.
- `moderate` - Sea state 3-4; wind 11-16 knots.
- `rough` - Sea state 5-6; wind 17-27 knots; heavy swell.
- `rainy,thunder` - Monsoonal squall with thunderstorm activity (directly recorded in sample row 1).

### 4.6 Landed Catch Offloading Classifications (`boat_gear_data.unloading_type`)
- `All` - Complete catch landed and offloaded at current landing site.
- `Partial` - Fractional catch landed; vessel transiting to secondary harbour for balance.
- `Baskets` - Offloading via manual crate/basket tally directly at the beach.

### 4.7 Commercial Catch Disposition Channels (`scientific_sampling_operation_utilization`)
Tracks post-harvest value chain distribution across 5 core channels:
1. `export_qty` / `export_value`: Fresh chilled / blast-frozen tuna directed to international export.
2. `local_qty` / `local_value`: Fresh fish auctioned for domestic wet-market distribution.
3. `dried_qty` / `dried_value`: Small pelagics, shark meat, or surplus directed to beach drying and salting.
4. `discard_qty` / `discard_value`: Damaged or non-commercial bycatch discarded at sea.
5. `trash`: Unsorted juvenile or decomposed fish sold for fish meal or aquaculture feeds.
