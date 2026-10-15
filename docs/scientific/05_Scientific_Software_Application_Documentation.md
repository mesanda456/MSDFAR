# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Software Application Documentation

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-APP-05
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Comprehensive software engineering documentation of the Yii2 MVC application layer, business services, controllers, Active Record domain models, and specialized reporting interfaces dedicated to the Scientific section.

---

## 1. Directory Structure of Scientific Components

The scientific sampling subsystem is cleanly modularized within the `backend/` application of DFAR-MS:

```
dfar_ms/backend/
├── controllers/
│   ├── ScientificController.php              # Administrative inspections, approvals & reporting
│   └── ScientificEnumerationController.php   # Field enumeration requests & sampling data ingress
├── services/
│   └── ScientificService.php                 # Core business service, transactional persistence & validation
├── models/
│   ├── ScientificEnumerationRequest.php       # Model for daily feasibility evaluations
│   ├── ScientificEnumerationRequestSearch.php # Filter & search model for requests
│   ├── ScientificData.php                     # Master session model
│   ├── ScientificDataSearch.php               # DataProvider for administrative session grid
│   ├── ScientificFleetData.php                # Summary fleet census model
│   ├── ScientificSamplingData.php             # Sampled craft identification model
│   ├── ScientificSamplingBoatGearData.php     # Nautical voyage & engine profile model
│   ├── ScientificSamplingGearData.php         # Technical gear specification model
│   ├── ScientificSamplingCatchData.php        # Landed catch by species model
│   ├── ScientificSamplingLengthDetails.php    # Fish length-frequency biometrics model
│   ├── ScientificSamplingOperationCost.php    # Trip economic expenditures model
│   ├── ScientificSamplingOperationUtilization.php # Post-harvest commercial disposition model
│   └── ScientificCatchReport.php              # Aggregation model for IOTC stock reporting
├── views/
│   ├── prototype/
│   │   ├── sdoScientific.php                  # Statistical Development Officer (SDO) field data entry form
│   │   ├── sdoScientificdatareqs.php          # SDO daily request history view
│   │   ├── fiScientific.php                   # Fisheries Inspector inspection queue
│   │   ├── fiScientificview.php               # Detailed FI inspection verification screen
│   │   ├── naraScientific.php                 # National Aquatic Resources Agency (NARA) research portal
│   │   └── naraScientificdatareqs.php         # NARA feasibility monitoring view
│   └── reports/
│       ├── scientific1.php                    # Monthly catch by gear and craft report
│       ├── scientific2.php                    # Species composition percentage breakdown
│       ├── scientific3.php                    # Length-frequency distribution histogram generator
│       ├── scientific4.php                    # Effort raising factor estimation report
│       └── scientificUtilReport.php           # Post-harvest utilization and value addition report
└── web/
    ├── js/scientific-craft-token.js           # Vessel autocomplete token engine
    └── jquery/scientificv5.js                 # Dynamic client-side multi-catch & biometric form generator
```

---

## 2. Core Controllers

### 2.1 `ScientificController.php`
Responsible for administrative review, territorial filtering, inspection approvals, and analytical exports.

| Action Method | HTTP Method | Target URL Route | Role / Clearance | Functional Responsibility |
| :--- | :---: | :--- | :--- | :--- |
| `actionIndex()` | `GET` | `/scientific/index` | FI / AD / Admin | Lists scientific sampling sessions scoped to officer's district/division. |
| `actionView($id)`| `GET` | `/scientific/view?id={id}`| FI / AD / NARA | Renders comprehensive session details (fleet, vessels, catches, biometrics, costs). |
| `actionApprove($id)`| `POST` | `/scientific/approve?id={id}`| FI / AD | Updates `approval_stage = 'Approved'`, logs `inspected_by = user.id`. |
| `actionReject($id)`| `POST` | `/scientific/reject?id={id}` | FI / AD | Updates `approval_stage = 'Rejected'`, logs feedback for enumerator correction. |
| `actionExport()` | `GET` | `/scientific/export` | NARA / AD / DG | Generates CSV / XLSX data extracts formatted for IOTC stock assessment. |

### 2.2 `ScientificEnumerationController.php`
Responsible for the daily field workflow of Statistical Development Officers (SDOs) at the landing centers.

| Action Method | HTTP Method | Target URL Route | Role / Clearance | Functional Responsibility |
| :--- | :---: | :--- | :--- | :--- |
| `actionCreate()` | `GET/POST`| `/scientific-enumeration/create`| SDO / Field Enumerator | Ingress point for multi-step sampling data submission. |
| `actionRequest()`| `POST` | `/scientific-enumeration/request`| SDO / Field Enumerator | Logs daily feasibility check (`can_continue = yes/no`, `reson`). |
| `actionSaveSession()`| `POST` | `/scientific-enumeration/save-session` | SDO / Field Enumerator | Atomic multi-table persistence via `ScientificService`. |

---

## 3. Business Service Layer: `ScientificService.php`

The `ScientificService` encapsulates all business rules, transactional guarantees, and statistical calculations:

```php
namespace backend\services;

use Yii;
use backend\models\ScientificData;
use backend\models\ScientificFleetData;
use backend\models\ScientificSamplingData;
use backend\models\ScientificSamplingBoatGearData;
use backend\models\ScientificSamplingGearData;
use backend\models\ScientificSamplingCatchData;
use backend\models\ScientificSamplingLengthDetails;
use backend\models\ScientificSamplingOperationCost;
use backend\models\ScientificSamplingOperationUtilization;

class ScientificService
{
    /**
     * Atomically persists a complete landing site sampling session with all child records.
     * Guarantees all-or-nothing ACID persistence across 10 tables.
     */
    public function persistScientificSession(array $payload, int $userId): array
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            // 1. Persist Master Session Header
            $session = new ScientificData();
            $session->district = $payload['district_id'];
            $session->division = $payload['division_id'];
            $session->landing_site = $payload['landing_site_id'];
            $session->start_time = $payload['start_time'];
            $session->end_time = $payload['end_time'];
            $session->added_by = $userId;
            $session->status = 1; // Draft / Submitted
            $session->approval_stage = 'Pending';
            
            if (!$session->save()) {
                throw new \Exception('Failed to save session header: ' . json_encode($session->errors));
            }

            // 2. Persist Fleet Census
            foreach ($payload['fleet_data'] as $fleetItem) {
                $fleet = new ScientificFleetData();
                $fleet->scientific_id = $session->id;
                $fleet->boat_type = $fleetItem['boat_type'];
                $fleet->sub_category = $fleetItem['sub_category'] ?? null;
                $fleet->gear_type = $fleetItem['gear_type'];
                $fleet->no_of_boats = $fleetItem['no_of_boats'];
                $fleet->status = 1;
                $fleet->save(false);
            }

            // 3. Persist Sampled Vessels & Nested Trees
            foreach ($payload['sampled_boats'] as $boatItem) {
                $vessel = new ScientificSamplingData();
                $vessel->scientific_id = $session->id;
                $vessel->boat_number = $boatItem['boat_number'];
                $vessel->status = 1;
                $vessel->save(false);

                // Voyage Profile
                $boatGear = new ScientificSamplingBoatGearData();
                $boatGear->sampling_data_id = $vessel->id;
                $boatGear->attributes = $boatItem['voyage_profile'];
                $boatGear->save(false);

                // Detailed Gears
                foreach ($boatItem['gears'] as $gearItem) {
                    $gear = new ScientificSamplingGearData();
                    $gear->boat_data_id = $boatGear->id;
                    $gear->attributes = $gearItem;
                    $gear->save(false);
                }

                // Catches & Length Biometrics
                foreach ($boatItem['catches'] as $catchItem) {
                    $catch = new ScientificSamplingCatchData();
                    $catch->craft_id = $vessel->id;
                    $catch->specie = $catchItem['specie_id'];
                    $catch->gear = $catchItem['gear_id'] ?? null;
                    $catch->weight_code = $catchItem['weight_code'] ?? 1;
                    $catch->weight = $catchItem['weight_kg'];
                    $catch->save(false);

                    if (!empty($catchItem['lengths'])) {
                        foreach ($catchItem['lengths'] as $lenVal) {
                            $len = new ScientificSamplingLengthDetails();
                            $len->sampling_data_id = $vessel->id;
                            $len->specie = $catch->specie;
                            $len->length = $lenVal['cm'];
                            $len->length_type = $lenVal['type'] ?? 1; // FL
                            $len->weight = $lenVal['weight_g'] ?? null;
                            $len->status = 1;
                            $len->save(false);
                        }
                    }
                }

                // Economics & Utilization
                if (!empty($boatItem['economics'])) {
                    $cost = new ScientificSamplingOperationCost();
                    $cost->sampling_data_id = $vessel->id;
                    $cost->attributes = $boatItem['economics'];
                    $cost->save(false);

                    if (!empty($boatItem['utilization'])) {
                        foreach ($boatItem['utilization'] as $utilItem) {
                            $util = new ScientificSamplingOperationUtilization();
                            $util->operation_cost_id = $cost->id;
                            $util->attributes = $utilItem;
                            $util->save(false);
                        }
                    }
                }
            }

            $transaction->commit();
            return ['success' => true, 'session_id' => $session->id];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Scientific Persistence Failure: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

---

## 4. Analytical Reporting Modules

1. **`scientific1.php` (Monthly Catch Summary)**: Produces monthly catch matrix disaggregated by boat category (`boat_type`) and gear type (`gear_type`).
2. **`scientific2.php` (Species Composition Breakdowns)**: Evaluates the percentage contribution of yellowfin tuna (*Thunnus albacares*), skipjack tuna (*Katsuwonus pelamis*), bigeye tuna, and demersal finfish to total landed catch.
3. **`scientific3.php` (Length-Frequency Histograms)**: Bins specimen length measurements (`length_details.length`) into 2cm intervals to assess cohort exploitation and calculate mean length at sexual maturity ($L_{50}$).
4. **`scientificUtilReport.php` (Post-Harvest Value Addition)**: Aggregates revenues from export channels vs local wet-markets and tracks discard rates.
