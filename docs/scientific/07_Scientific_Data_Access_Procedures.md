# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: Data-Access Procedures

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-DAP-07
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Data access protocols, query patterns, eager loading optimization, transactional write procedures, concurrency control, and parameterized statement rules dedicated to scientific data tables.

---

## 1. Application-Level Data Access Architecture

Data operations within the Scientific Sampling Subsystem are handled exclusively through the **Yii2 Database Abstraction Layer** using **PHP Data Objects (PDO)**. Direct interpolation of raw user parameters into SQL statements is strictly prohibited.

```
Controller / Service Layer
           │
           ▼
ActiveRecord Domain Models (ScientificData, ScientificSamplingCatchData)
           │
           ▼
Yii2 QueryBuilder & Parameter Binding (:district, :specie, :status)
           │
           ▼
PHP Data Objects (PDO) Prepared Statements
           │
           ▼
MySQL 8.0 / MariaDB InnoDB Engine
```

---

## 2. Optimized Read & Query Protocols

### 2.1 Eager Loading & N+1 Prevention
To achieve sub-50ms query response times when rendering comprehensive sampling session screens, nested relations MUST be eager-loaded using `with()`:

```php
// Standard Eager Loading Query for Scientific Session Review
$sessions = ScientificData::find()
    ->with([
        'districtRelation',
        'divisionRelation',
        'landingSiteRelation',
        'fleetData',
        'samplingData.boatGearData.gearData',
        'samplingData.catchData',
        'samplingData.lengthDetails',
        'samplingData.operationCost.operationUtilization'
    ])
    ->where(['district' => $officerDistrictId])
    ->andWhere(['status' => 1])
    ->orderBy(['start_time' => SORT_DESC])
    ->limit(25)
    ->all();
```

### 2.2 Streaming Batch Pagination for Large Stock Assessment Exports
When exporting multi-year catch logs (which currently exceed 120,000 kg across 194 catch rows and thousands of biometric measurements) to NARA or IOTC formats, cursor batches must be used to keep memory consumption under 32MB:

```php
// Memory-Efficient Cursor Processing for IOTC Export
$catchQuery = ScientificSamplingCatchData::find()
    ->with(['craft', 'craft.scientific'])
    ->orderBy(['id' => SORT_ASC]);

foreach ($catchQuery->batch(200) as $catchBatch) {
    foreach ($catchBatch as $record) {
        $csvWriter->write([
            $record->craft->scientific->start_time,
            $record->craft->boat_number,
            $record->specie,
            $record->weight,
            $record->weight_code
        ]);
    }
}
```

---

## 3. Transactional Write Procedures (ACID Guarantees)

Multi-table scientific sessions must execute within explicit ACID transactions. If an insert fails on biometric length measurements, all preceding master session, fleet, and catch inserts are cleanly rolled back:

```php
public function saveScientificSession(array $sessionData): int
{
    $transaction = Yii::$app->db->beginTransaction();
    try {
        $master = new ScientificData();
        $master->attributes = $sessionData['header'];
        if (!$master->save()) {
            throw new \Exception('Failed saving session header');
        }

        foreach ($sessionData['fleet'] as $fleetRow) {
            $fleet = new ScientificFleetData();
            $fleet->scientific_id = $master->id;
            $fleet->attributes = $fleetRow;
            $fleet->save(false);
        }

        foreach ($sessionData['boats'] as $boatRow) {
            $boat = new ScientificSamplingData();
            $boat->scientific_id = $master->id;
            $boat->boat_number = $boatRow['boat_number'];
            $boat->save(false);

            // Persist associated catch data
            foreach ($boatRow['catches'] as $catchRow) {
                $catch = new ScientificSamplingCatchData();
                $catch->craft_id = $boat->id;
                $catch->attributes = $catchRow;
                $catch->save(false);
            }
        }

        $transaction->commit();
        return $master->id;
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error("Aborted Scientific Session Write: " . $e->getMessage(), __METHOD__);
        throw $e;
    }
}
```

---

## 4. Concurrency Control & Row Locking

When a Fisheries Inspector (FI) or Assistant Director (AD) approves or updates a field enumeration session, **Pessimistic Row-Level Locking** (`FOR UPDATE`) is enforced to prevent concurrent modification collisions:

```php
// Concurrency-Safe Inspection Approval Procedure
$session = ScientificData::findBySql(
    "SELECT * FROM scientific_data WHERE id = :id FOR UPDATE",
    [':id' => $sessionId]
)->one();

if ($session && $session->approval_stage === 'Pending') {
    $session->approval_stage = 'Approved';
    $session->inspected_by = Yii::$app->user->id;
    $session->save(false);
}
```

---

## 5. SQL Injection Prevention & Parameter Binding Standards

1. Direct variable interpolation inside SQL strings is strictly banned across all scientific controllers and search models.
2. Parameterized arrays must be used:
   ```php
   // COMPLIANT: Auto-parameterized through ActiveQuery
   $requests = ScientificEnumerationRequest::find()
       ->where(['user' => $userId, 'status' => 1])
       ->all();

   // COMPLIANT: Explicit PDO binding in QueryBuilder
   $summary = (new \yii\db\Query())
       ->select(['specie', 'SUM(weight) AS total_kg'])
       ->from('scientific_sampling_catch_data')
       ->where('weight > :minWeight', [':minWeight' => 0.0])
       ->groupBy('specie')
       ->all();
   ```
