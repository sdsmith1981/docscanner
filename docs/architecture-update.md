# Architecture Guidelines Update

## Additional Requirements Added

### 1. **PHP Enums Usage**

- **Requirement**: Use PHP enums for fixed sets of values where applicable
- **Implementation**:
    - Added `Enums/` directory to file structure
    - Updated documentation templates to include enum sections
    - Added enum testing requirements in testing skill
    - Provided enum example in database schema template

### 2. **Thin Controllers & Service Classes**

- **Requirement**: Classes should be kept thin. Use FormRequest for validation and Laravel collections/resources for responses. Service classes should contain majority of core logic.
- **Implementation**:
    - Updated backend skill to emphasize service classes for core business logic
    - Added `Http/Requests/` directory for FormRequest classes
    - Updated file structure to show clear separation of concerns
    - Enhanced testing requirements to cover service classes, FormRequests, and API resources
    - Updated documentation templates to include sections for each architectural component

### 3. **TypeScript Preference**

- **Requirement**: On the front end, TypeScript is preferred over JavaScript
- **Implementation**:
    - Updated all frontend documentation to emphasize TypeScript preference
    - Added TypeScript-specific requirements in Vue.js skill
    - Enhanced file structure to include dedicated `Types/` directory
    - Updated testing requirements to cover TypeScript interfaces and types

## Updated File Structure

### Backend Architecture

```
app/
├── Models/                  # Eloquent models
├── Http/Controllers/Api/    # API controllers (kept thin)
├── Http/Requests/           # FormRequest validation classes
├── Http/Resources/          # API resource transformation
├── Services/               # Core business logic
├── Enums/                  # PHP enums for fixed values
└── Rules/                  # Custom validation rules
```

### Frontend Architecture

```
resources/
├── js/
│   ├── Components/          # Vue components
│   ├── Pages/             # Page components
│   ├── Stores/            # Pinia stores
│   ├── Types/             # TypeScript type definitions
│   ├── Composables/       # Reusable composition logic
│   └── app.ts
└── css/
    └── app.css
```

## Quality Checklist Updates

### Backend Development

- [ ] Controllers are thin and delegate to service classes
- [ ] All validation logic is in FormRequest classes
- [ ] All API responses use Laravel collections/resources
- [ ] Core business logic is in service classes
- [ ] Enums are used for fixed value sets
- [ ] Code passes all quality checks (Pint, PHPStan, Magic Numbers)

### Frontend Development

- [ ] All files use TypeScript (no JavaScript)
- [ ] Proper TypeScript interfaces are defined
- [ ] Code is formatted with ESLint + Prettier
- [ ] TypeScript compilation succeeds

### Testing

- [ ] Service classes are properly unit tested
- [ ] FormRequest validation rules are tested
- [ ] API resource transformations are tested
- [ ] Enum functionality is tested

## Documentation Templates Updated

All documentation templates now include:

- **Backend Changes section** with separate subsections for:
    - Models
    - Enums
    - Service Classes
    - FormRequest Classes
    - API Resources
    - API Endpoints

- **Database Schema** with enum examples:
    ```sql
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft'
    ```

## Benefits of This Architecture

1. **Separation of Concerns**: Each component has a single responsibility
2. **Maintainability**: Code is organised and easier to navigate
3. **Testability**: Each layer can be tested in isolation
4. **Type Safety**: TypeScript provides compile-time error checking
5. **Code Reusability**: Service classes and enums promote code reuse
6. **Consistency**: Standardised patterns across the application

## Migration Strategy

For existing code, gradually refactor to:

1. Extract business logic from controllers to service classes
2. Move validation logic to FormRequest classes
3. Replace magic strings/numbers with enums
4. Add API resource classes for response formatting
5. Convert JavaScript files to TypeScript

This architectural approach follows Laravel best practices and ensures the codebase remains scalable and maintainable.
