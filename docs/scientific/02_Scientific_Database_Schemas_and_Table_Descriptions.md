# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Database Schemas and Table Descriptions

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-DB-02
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Exhaustive physical and logical schema specifications for all 10 core scientific enumeration tables and 3 supporting geographic hierarchy tables, reflecting exact database column names, datatypes, nullability, constraints, live record volumes, and actual database row samples.

---

## 1. Scientific Geographic Hierarchy Tables

### 1.1 `m_fi_district` (Coastal Administrative District Master)
*Total Rows in DB: 16 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique District Identifier |
| `name` | `varchar(100)` | NO | | NULL | Official Coastal District Name (e.g. Colombo, Batticaloa, Galle, Matara) |
| `code` | `varchar(10)` | YES | | NULL | Standard 3-letter district identifier code (e.g. `CBO`, `BCO`, `GAL`, `MAT`) |
| `status` | `int(11)` | NO | | 1 | Operational status flag (1=Active, 0=Inactive) |

*Production Sample*:
```json
{"id": 4, "name": "Batticaloa", "status": 1, "code": "BCO"}
{"id": 5, "name": "Colombo", "status": 1, "code": "CBO"}
```

---

### 1.2 `m_division` (Fisheries Inspector Division Master)
*Total Rows in DB: 322 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Division Identifier |
| `district_id` | `int(11)` | NO | **FK** | NULL | Foreign key reference to `m_fi_district.id` |
| `name` | `varchar(100)` | NO | | NULL | Official Division Name (e.g. Negombo, Beruwala, Trincomalee Town) |
| `status` | `int(11)` | NO | | 1 | Operational status flag |

---

### 1.3 `m_landing_site` (Fish Landing Center Master)
*Total Rows in DB: 4,559 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Landing Site Identifier |
| `division_id` | `int(11)` | NO | **FK** | NULL | Foreign key reference to `m_division.id` |
| `name` | `varchar(200)` | NO | | NULL | Name of landing center / fishery harbour / anchor point |
| `status` | `int(11)` | NO | | 1 | Operational status flag |

---

## 2. The 10 Core Scientific Enumeration & Sampling Tables

---

### 2.1 `scientific_enumeration_request`
*Functional Role: Records whether an enumerator can carry out daily enumeration at a landing site, or documents the obstruction reason.*
*Total Rows in DB: 73 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Request Record Identifier |
| `user` | `int(11)` | NO | **FK** | NULL | Enumerator User ID (`user.id`) |
| `request_date` | `date` | YES | | NULL | System date when request was generated |
| `date` | `datetime` | YES | | NULL | Observation timestamp of proposed sampling |
| `can_continue` | `varchar(100)` | YES | | NULL | Feasibility flag string (`yes`, `no`) |
| `reson` | `varchar(200)` | YES | | NULL | Obstruction explanation if unable to sample (weather, strike, zero landings) |
| `status` | `int(11)` | NO | | 1 | Verification state (1=Pending, 2=Approved by FI) |
| `district` | `int(11)` | YES | **FK** | 0 | Target District (`m_fi_district.id`) |
| `division` | `int(11)` | YES | **FK** | 0 | Target Division (`m_division.id`) |
| `landing_site` | `int(11)` | YES | **FK** | 0 | Target Landing Site (`m_landing_site.id`) |

*Production Sample*:
```json
{
  "id": 55,
  "user": 204,
  "request_date": null,
  "date": "2023-11-07 07:18:00",
  "can_continue": "yes",
  "reson": "Weather clear, fleet active",
  "status": 1,
  "district": 4,
  "division": 12,
  "landing_site": 84
}
```

---

### 2.2 `scientific_data`
*Functional Role: Master session record for completed landing site enumeration and catch sampling.*
*Total Rows in DB: 112 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Scientific Session Identifier |
| `district` | `int(11)` | NO | **FK** | NULL | Administrative District ID (`m_fi_district.id`) |
| `division` | `int(11)` | NO | **FK** | NULL | Inspector Division ID (`m_division.id`) |
| `landing_site` | `int(11)` | NO | **FK** | NULL | Landing Site ID (`m_landing_site.id`) |
| `start_time` | `datetime` | NO | | NULL | Session start timestamp at landing site |
| `end_time` | `datetime` | NO | | NULL | Session conclusion timestamp at landing site |
| `added_by` | `int(11)` | NO | **FK** | NULL | Enumerator officer ID (`user.id`) |
| `status` | `int(11)` | NO | | 1 | Session status (1=Draft, 10=Submitted, 200=Approved) |
| `approval_stage` | `varchar(50)` | YES | | NULL | Approval tier text (`Pending`, `Approved`, `Rejected`) |
| `inspected_by` | `int(11)` | YES | **FK** | NULL | Verifying Fisheries Inspector ID (`user.id`) |

*Production Sample*:
```json
{
  "id": 2,
  "district": 4,
  "division": 4,
  "landing_site": 1,
  "start_time": "2023-06-02 17:59:25",
  "end_time": "2023-06-02 17:59:25",
  "added_by": 3,
  "status": 1,
  "approval_stage": "Pending",
  "inspected_by": null
}
```

---

### 2.3 `scientific_fleet_data`
*Functional Role: Summary census of all boats operated in the landing site during the enumeration session.*
*Total Rows in DB: 145 (4,433 total boats counted) | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Fleet Record Identifier |
| `scientific_id` | `int(11)` | NO | **FK** | NULL | Reference to `scientific_data.id` |
| `boat_type` | `int(11)` | NO | **FK** | NULL | Craft class category (`m_boat_types.id`) (e.g. OFRP, NBSB, IMUL) |
| `sub_category` | `int(11)` | YES | **FK** | NULL | Craft technical sub-category (`m_boat_sub_category.id`) |
| `gear_type` | `int(11)` | NO | **FK** | NULL | Fishing gear deployed (`m_gear_types.id`) |
| `no_of_boats` | `int(11)` | NO | | 0 | Count of active craft operating at landing site |
| `status` | `int(11)` | NO | | 1 | Record verification status |

*Production Sample*:
```json
{
  "id": 1,
  "scientific_id": 2,
  "boat_type": 1,
  "sub_category": 1,
  "gear_type": 1,
  "no_of_boats": 100,
  "status": 1
}
```

---

### 2.4 `scientific_sampling_data`
*Functional Role: Registration details of individually selected vessels sampled for biometric and economic profiling.*
*Total Rows in DB: 120 (45 unique vessels) | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Sampled Vessel Identifier |
| `scientific_id` | `int(11)` | NO | **FK** | NULL | Reference to parent session (`scientific_data.id`) |
| `boat_number` | `varchar(200)` | NO | **IDX** | NULL | Official DFAR vessel registration / MEA number (e.g. `IMULA333BCO`) |
| `status` | `int(11)` | NO | | 1 | Record validation status |

*Production Sample*:
```json
{
  "id": 1,
  "scientific_id": 3,
  "boat_number": "IMULA333BCO",
  "status": 1
}
```

---

### 2.5 `scientific_sampling_boat_gear_data`
*Functional Role: Nautical profile, engine HP, departure/arrival metrics, crew size, and weather conditions for the sampled boat.*
*Total Rows in DB: 99 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Record Identifier |
| `sampling_data_id` | `int(11)` | NO | **FK** | NULL | Pointer to `scientific_sampling_data.id` |
| `fishey_type` | `int(11)` | YES | **FK** | NULL | Fishery domain classification (`m_fishery_types.id`) |
| `sub_category` | `int(11)` | YES | **FK** | NULL | Boat sub-class (`m_boat_sub_category.id`) |
| `engine_hp` | `varchar(11)` | YES | | NULL | Propulsion power in Horsepower (HP) |
| `departure_date` | `date` | YES | | NULL | Date vessel sailed from departure port |
| `departure_time` | `time` | YES | | NULL | Time vessel sailed from departure port |
| `departure_district`| `int(11)` | YES | **FK** | NULL | District of departure (`m_fi_district.id`) |
| `departure_division`| `int(11)` | YES | **FK** | NULL | Division of departure (`m_division.id`) |
| `depature_port` | `int(11)` | YES | **FK** | NULL | Departure port / harbour ID (`m_landing_site.id`) |
| `arrival_date` | `date` | YES | | NULL | Date vessel arrived and landed catch |
| `weather` | `varchar(200)` | YES | | NULL | Atmospheric and sea condition (`rainy,thunder`, `calm`, `rough`) |
| `crew_members_count`| `int(11)` | YES | | 1 | Total number of crew onboard |
| `unloading_type` | `varchar(200)` | YES | | NULL | Landed catch offloading method (`All`, `Partial`, `Baskets`) |
| `gear_setting_time`| `varchar(100)` | YES | | NULL | Time of day gear deployed |
| `days` | `int(11)` | YES | | 0 | Total voyage duration in days |
| `hours` | `int(11)` | YES | | 0 | Voyage hours at sea |
| `remark` | `varchar(200)` | YES | | NULL | Field enumerator remarks |
| `status` | `int(11)` | NO | | 1 | Record validation status |

*Production Sample*:
```json
{
  "id": 1,
  "sampling_data_id": 7,
  "fishey_type": 2,
  "sub_category": 2,
  "engine_hp": "22",
  "departure_date": "2023-06-15",
  "departure_time": "17:23:00",
  "departure_district": 4,
  "departure_division": 4,
  "depature_port": 1,
  "weather": "rainy,thunder",
  "arrival_date": "2023-06-06",
  "remark": "33",
  "crew_members_count": 33,
  "unloading_type": "All",
  "gear_setting_time": "1",
  "days": 3,
  "hours": 3,
  "status": 1
}
```

---

### 2.6 `scientific_sampling_gear_data`
*Functional Role: Technical gear metrics, target species, soaking hours, and fishing depth.*
*Total Rows in DB: 100 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Gear Record Identifier |
| `boat_data_id` | `int(11)` | NO | **FK** | NULL | Pointer to `scientific_sampling_boat_gear_data.id` |
| `gear` | `int(11)` | NO | **FK** | NULL | Main gear code (`m_gear_types.id`) |
| `target_species` | `int(11)` | YES | **FK** | NULL | Primary target species (`m_fish_types.id`) |
| `operations_per_trip`| `int(11)` | YES | | 1 | Number of fishing sets / net casts per trip |
| `fishing_time_days`| `double` | YES | | 0 | Active fishing effort duration in days |
| `fishing_time_hours`| `double` | YES | | 0 | Active fishing effort duration in hours |
| `fishing_depth` | `double` | YES | | NULL | Water depth of fishing operation (meters) |
| `g_code` | `int(11)` | YES | | NULL | Standard gear classification code |
| `extra` | `varchar(500)` | YES | | NULL | Supplemental gear dimensions (mesh, hooks, length) |
| `type` | `varchar(100)` | YES | | NULL | Role flag (`mainGear`, `subsidiaryGear`) |
| `status` | `int(11)` | NO | | 1 | Record validation status |

*Production Sample*:
```json
{
  "id": 1,
  "boat_data_id": 14,
  "gear": 1,
  "target_species": 4,
  "operations_per_trip": 33,
  "fishing_time_days": 3,
  "fishing_time_hours": 3,
  "fishing_depth": 3,
  "g_code": 51,
  "extra": null,
  "type": "mainGear",
  "status": 1
}
```

---

### 2.7 `scientific_sampling_catch_data`
*Functional Role: Disaggregated landed catch log by species, weight code, and landed weight.*
*Total Rows in DB: 194 (120,032.50 kg landed catch) | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Catch Record Identifier |
| `craft_id` | `int(11)` | NO | **FK** | NULL | Reference to `scientific_sampling_data.id` |
| `specie` | `int(11)` | NO | **FK** | NULL | Marine fish species ID (`m_fish_types.id`) |
| `gear` | `int(11)` | YES | **FK** | NULL | Specific gear used to harvest species |
| `weight_code` | `int(11)` | YES | | NULL | Unit conversion code (1=kg, 2=tonnes, 3=crates) |
| `weight` | `double` | NO | | 0 | Total landed weight |

*Production Sample*:
```json
{
  "id": 1,
  "craft_id": 11,
  "specie": 0,
  "gear": 1,
  "weight_code": 3,
  "weight": 10
}
```

---

### 2.8 `scientific_sampling_length_details`
*Functional Role: Individual specimen biological morphometrics (length frequency measurements).*
*Total Rows in DB: 73 (Lengths: 3cm to 555cm, Avg: 96.75cm) | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Specimen Measurement Identifier |
| `sampling_data_id` | `int(11)` | NO | **FK** | NULL | Pointer to `scientific_sampling_data.id` |
| `gear` | `int(11)` | YES | **FK** | NULL | Gear type used |
| `specie` | `int(11)` | NO | **FK** | NULL | Species identifier (`m_fish_types.id`) |
| `specie_count` | `int(11)` | YES | | 1 | Number of specimens in this length class |
| `length` | `double` | NO | | NULL | Measured specimen length in centimeters |
| `length_type` | `int(11)` | NO | | 1 | Length type code (1=Fork Length FL, 2=Total Length TL, 3=Standard Length SL) |
| `length_code` | `int(11)` | YES | | 1 | Measurement unit code |
| `weight` | `double` | YES | | NULL | Specimen individual weight |
| `weight_code` | `int(11)` | YES | | NULL | Weight unit code |
| `status` | `int(11)` | NO | | 1 | Biometric verification status |

*Production Sample*:
```json
{
  "id": 1,
  "sampling_data_id": 32,
  "gear": 1,
  "specie": 1,
  "specie_count": 2,
  "weight": 4,
  "weight_code": 1,
  "length_type": 1,
  "length_code": 1,
  "length": 7,
  "status": 1
}
```

---

### 2.9 `scientific_sampling_operation_cost`
*Functional Role: Trip financial expenditures incurred by boat owner for voyage analysis.*
*Total Rows in DB: 61 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Cost Record Identifier |
| `sampling_data_id` | `int(11)` | NO | **FK** | NULL | Pointer to `scientific_sampling_data.id` |
| `fuel_qty` | `double` | YES | | 0 | Fuel consumed in Liters |
| `fuel_price` | `double` | YES | | 0 | Fuel unit price (LKR/Liter) |
| `ice_qty` | `double` | YES | | 0 | Crushed ice blocks purchased |
| `ice_price` | `double` | YES | | 0 | Ice unit cost |
| `bait_qty` | `double` | YES | | 0 | Bait volume purchased (kg) |
| `bait_price` | `double` | YES | | 0 | Bait unit cost (LKR/kg) |
| `labour_cost` | `double` | YES | | 0 | Total crew wages / crew share advance paid |
| `food_water` | `double` | YES | | 0 | Cost of food provisions and freshwater |
| `other` | `double` | YES | | 0 | Harbour fees, unloading dues, maintenance |
| `remark` | `varchar(500)` | YES | | NULL | Enumerator economic notes |

*Production Sample*:
```json
{
  "id": 1,
  "sampling_data_id": 21,
  "fuel_qty": 324,
  "fuel_price": 34,
  "ice_qty": 342,
  "ice_price": 324,
  "bait_qty": 342,
  "bait_price": 2,
  "labour_cost": 324,
  "food_water": 324,
  "other": 234,
  "remark": "324"
}
```

---

### 2.10 `scientific_sampling_operation_utilization`
*Functional Role: Post-harvest catch commercial disposition tracking (exports, local fresh, salting, discards, trash).*
*Total Rows in DB: 56 | Storage Engine: InnoDB*

| Column | Physical Type | Nullable | Key | Default | Description |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `id` | `int(11)` | NO | **PK** | Auto Inc | Unique Utilization Record Identifier |
| `operation_cost_id`| `int(11)` | NO | **FK** | NULL | Pointer to `scientific_sampling_operation_cost.id` |
| `specie` | `int(11)` | NO | **FK** | NULL | Species identifier (`m_fish_types.id`) |
| `export_qty` | `double` | YES | | 0 | Quantity directed to air / frozen export (kg) |
| `export_value` | `double` | YES | | 0 | Export sales proceeds in LKR |
| `local_qty` | `double` | YES | | 0 | Quantity sold at domestic wholesale/retail markets (kg) |
| `local_value` | `double` | YES | | 0 | Local sales proceeds in LKR |
| `dried_qty` | `double` | YES | | 0 | Quantity directed to drying / salting / Maldive fish (kg) |
| `dried_value` | `double` | YES | | 0 | Dried fish revenue in LKR |
| `discard_qty` | `double` | YES | | 0 | Catch discarded at sea due to spoilage or non-target status (kg) |
| `discard_value` | `double` | YES | | 0 | Discard financial loss in LKR |
| `trash` | `int(11)` | YES | | 0 | Quantity sold as trash fish for animal feed / fertilizer |
| `status` | `int(11)` | NO | | 1 | Record validation status |

*Production Sample*:
```json
{
  "id": 1,
  "operation_cost_id": 1,
  "specie": 0,
  "export_qty": 234,
  "export_value": 23,
  "local_qty": 234,
  "local_value": 23,
  "dried_qty": 243,
  "dried_value": 432,
  "discard_qty": 42,
  "discard_value": 2,
  "trash": 0,
  "status": 1
}
```
