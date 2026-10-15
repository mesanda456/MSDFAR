# Department of Fisheries and Aquatic Resources (DFAR)
# Scientific Sampling Subsystem: User Permission and Access-Control Procedures

---

### Document Overview
- **Document Code**: DFAR-SCI-DOC-SEC-08
- **Domain**: Marine Scientific Catch Sampling & Enumeration Subsystem
- **Scope**: Role-Based Access Control (RBAC), authentication mechanisms, row-level territorial segmentation, and field verification governance dedicated to scientific enumeration.

---

## 1. Scientific Organizational Role Hierarchy

The scientific subsystem implements a strict multi-tier clearance hierarchy:

```
+-----------------------------------------------------------------------------------------+
|                                    RBAC ROLE MATRIX                                     |
+-----------------------------------------------------------------------------------------+
|  Role Name                      | User Type | Clearance Scope | Permitted Operations    |
+---------------------------------+-----------+-----------------+-------------------------+
|  Statistical Officer (SDO)      | 5 (DO/SDO)| Field / Beach   | Create Requests, Insert |
|  (Field Enumerator)             |           | Assigned Site   | Sessions, Catches, Costs|
+---------------------------------+-----------+-----------------+-------------------------+
|  Fisheries Inspector (FI)       | 2 (FI)    | Territorial     | Verify Sessions, Review |
|                                 |           | Division Scope  | Outliers, Approve/Reject|
+---------------------------------+-----------+-----------------+-------------------------+
|  Assistant Director (AD)        | 3 (AD)    | District Scope  | Endorse Reports, District|
|                                 |           | (16 Districts)  | SDO Work Allocation     |
+---------------------------------+-----------+-----------------+-------------------------+
|  NARA Research Scientist        | 8 (NARA)  | National Scope  | Read-Only Query Access, |
|                                 |           | (All Districts) | Stock Modeling Extracts |
+---------------------------------+-----------+-----------------+-------------------------+
|  System Administrator           | 11 (ADMIN)| Global System   | Master Lookups, DB Audit|
+-----------------------------------------------------------------------------------------+
```

---

## 2. Row-Level Security (RLS) & Jurisdiction Scoping

Access to scientific session data is dynamically constrained by the officer's administrative jurisdiction declared in `profile_officer`:

```php
// ActiveQuery Jurisdiction Enforcement for Scientific Data
public static function find()
{
    $query = parent::find();
    $user = Yii::$app->user->identity;

    if (!$user || $user->type == Constant::ADMIN || $user->type == Constant::DG) {
        return $query; // Unrestricted access for Executives & DBAs
    }

    $profile = ProfileOfficer::findOne(['nic' => $user->nic]);
    if (!$profile) {
        return $query->where('0=1'); // Deny if profile missing
    }

    if ($user->type == Constant::FI) {
        // FI can only view and approve sessions in their assigned Division
        return $query->andWhere(['division' => $profile->division]);
    }

    if ($user->type == Constant::AD) {
        // AD can view all sessions across their administrative District
        return $query->andWhere(['district' => $profile->district]);
    }

    if ($user->type == Constant::DO) {
        // SDO can view sessions they authored or at assigned landing sites
        return $query->andWhere(['added_by' => $user->id]);
    }

    return $query;
}
```

---

## 3. Immutable Session Freezing & Audit Trail

1. **State Transition Controls**:
   - `Pending` (Initial entry by SDO): Open for editing and correction by the authoring SDO (`added_by`).
   - `Approved` (Endorsed by FI): The record is frozen and converted to **READ-ONLY**. Any subsequent `UPDATE` or `DELETE` attempt throws an `AccessDeniedHttpException`.
2. **Audit Attribution**:
   - Every session records `added_by` (`user.id`) and observation timestamps (`start_time`, `end_time`).
   - Every verification records `inspected_by` (`user.id`) and the timestamp of approval.
