# Department of Fisheries and Aquatic Resources (DFAR)
## Data Access Procedures

---

### Document Overview
- **Document Code**: DFAR-DOC-DAP-07
- **System**: Department of Fisheries and Aquatic Resources Management System (DFAR-MS)
- **Target Audience**: Database Administrators (DBAs), Application Developers, Security Auditors
- **Scope**: Data access protocols, query standards, transaction handling, injection prevention, and direct database access governance.

---

## 1. Application-Level Data Access Architecture

Data access in the DFAR-MS application is channeled exclusively through the **Yii2 Database Abstraction Layer** using **PHP Data Objects (PDO)**:

```
Controller Layer  ──>  Service Layer  ──>  ActiveRecord ORM  ──>  Yii DB Layer (PDO)  ──>  MySQL Server
```

### 1.1 Object-Relational Mapping (ActiveRecord)
All business entities inherit from `yii\db\ActiveRecord`. This ensures that table schema, data type conversions, validation rules, and relational joins are declared in strongly-typed model definitions.

```php
// Example: Validated query execution using ActiveRecord
$boat = BoatNumbers::find()
    ->where(['boat_number' => $requestedNumber])
    ->andWhere(['status' => Constant::Approved])
    ->one();
```

### 1.2 Eager Loading & N+1 Query Prevention
To maintain high throughput across intensive reporting operations, all relational queries MUST utilize eager loading (`with()`) rather than lazy relational lookups:

```php
// Standard Eager Loading Practice
$submissions = Bsc1Submission::find()
    ->with(['division', 'boats', 'registrations'])
    ->where(['period_date' => $targetMonth])
    ->all();
```

---

## 2. SQL Injection Prevention & Parameter Binding

Direct string concatenation in SQL statements is **strictly prohibited** across the entire codebase. All dynamic inputs must be bound through PDO parameters.

### Permitted Patterns

1. **ActiveRecord Array Syntax (Auto-parameterized)**:
   ```php
   $user = User::find()->where(['nic' => $userInputNic])->one();
   ```

2. **QueryBuilder with Parameter Placeholders**:
   ```php
   $rows = (new \yii\db\Query())
       ->select(['id', 'boat_number', 'created'])
       ->from('boat_numbers')
       ->where('fisheries_district = :dist AND status = :stat', [
           ':dist' => $districtId,
           ':stat' => Constant::Approved,
       ])
       ->all();
   ```

3. **Raw SQL via Command with Explicit Binding**:
   ```php
   $command = Yii::$app->db->createCommand(
       'SELECT COUNT(*) FROM national_license WHERE valid_to < :now AND status = :active'
   );
   $command->bindValue(':now', date('Y-m-d'));
   $command->bindValue(':active', Constant::Active);
   $count = $command->queryScalar();
   ```

---

## 3. Database Transaction Management

Any operation modifying multiple related records (such as processing an ownership transfer and logging the audit event) must be encapsulated in an atomic database transaction.

### Transaction Procedure Template

```php
$transaction = Yii::$app->db->beginTransaction();
try {
    // Step 1: Update vessel record
    $boat->status = Constant::Transferred;
    if (!$boat->save(false)) {
        throw new \Exception('Failed to update boat record.');
    }

    // Step 2: Append audit log entry
    $log = new BoatNumberOwnersLog();
    $log->boat_number_id = $boat->id;
    $log->previous_owner_id = $currentOwnerId;
    $log->new_owner_id = $newOwnerId;
    $log->transfer_date = date('Y-m-d H:i:s');
    if (!$log->save(false)) {
        throw new \Exception('Failed to append ownership audit log.');
    }

    // Step 3: Commit transaction
    $transaction->commit();
} catch (\Throwable $e) {
    // Step 4: Full rollback on failure
    $transaction->rollBack();
    Yii::error("Vessel transfer transaction failed: " . $e->getMessage(), 'database');
    throw $e;
}
```

---

## 4. Direct Database Access Protocols & Governance

### 4.1 Production Environment Restrictions
1. **No Direct Remote Root Login**: Direct TCP access to port 3306 on production database servers is blocked from external networks by firewall rules.
2. **Dedicated Least-Privilege Application User**:
   - The application connects using a dedicated user (`dfar_app`) with permissions restricted to `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `EXECUTE`.
   - Structural permissions (`DROP`, `ALTER`, `GRANT`) are disabled for the web application user.
3. **Database Migration Execution**:
   - Schema modifications are executed exclusively through version-controlled console migrations:
     ```bash
     php yii migrate --interactive=0
     ```

### 4.2 Local & Staging Development Access
- Local development connects via loopback (`127.0.0.1:3306`) as configured in [`common/config/main-local.php`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/common/config/main-local.php).
- Administrative database inspections are conducted via secure local terminal utilities:
  ```bash
  /usr/local/mysql/bin/mysql -u root -p dfar_ms
  ```

---

## 5. Sensitive Data Masking & Log Sanitization

To comply with data privacy policies and cybersecurity standards, sensitive fields must never be exposed in application traces or server logs:

1. **Redacted Fields**: Password hashes, session tokens, CSRF tokens, bearer tokens, and auth keys are masked using regex sanitization filters in [`ErrorController::redactSensitiveLogData()`](file:///Users/mesandasethumika/Desktop/MSDFAR/dfar_ms/backend/controllers/ErrorController.php):
   ```php
   '/(password|passwd|token|access_token|secret|api_key|_csrf)\b(\s*[:=]\s*)([^\s,;\]\}]+)/i' => '$1$2[REDACTED]'
   ```
2. **Trilingual Error Logging**: In the event of query failure, users are displayed an uninformative, secure public reference code (`ERR-YYYYMMDD-...`), while the complete SQL query and stack trace are logged privately in `@runtime/logs/app.log`.
