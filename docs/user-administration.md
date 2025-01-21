# User Administration

The user administration concept supports four distinct roles for each module. These roles define the level of access 
and permissions for managing module-specific entities. The different modules include entities like Players, Playlists, 
Templates, Channels, associated Users, and more.

Every module with own user administration abilities shoud derivate from class [AbstractAclValidator](
..%2Fsrc%2FFramework%2FCore%2FAcl%2FAbstractAclValidator.php).

## Role Types

### 1. **Module Administrator (Module Admin)**
- **Permissions:**
    - Full access to all entities within the module.
    - Can view and administer Reseller-specific entities.
- **Responsibilities:**
    - Manage and configure all module-specific settings and data.
    - Oversee Reseller accounts and their entities.

### 2. **Sub Administrator (SubAdmin)**
- **Permissions:**
    - Access is limited to module-specific entities associated with the Reseller/Company they grant access to.
- **Responsibilities:**
    - Manage and configure entities only within their designated scope.

### 3. **Editor**
- **Permissions:**
    - Can view, utilize, and edit assigned entities within the module.
- **Responsibilities:**
    - Modify and use module-specific entities as permitted.

### 4. **Viewer**
- **Permissions:**
    - Can only view and utilize the assigned entities within the module.
- **Responsibilities:**
    - Restricted to viewing and basic usage of entities.

## Summary Table

| Role            | View Entities | Edit Entities | Administer Resellers | Scope              |
|-----------------|---------------|---------------|----------------------|--------------------|
| Module Admin    | ✓            | ✓            | ✓                 | All entities in the module |
| SubAdmin        | ✓            | ✓            |                      | Reseller-specific   |
| Editor          | ✓            | ✓            |                      | Assigned entities   |
| Viewer          | ✓            |               |                      | Assigned entities   |

