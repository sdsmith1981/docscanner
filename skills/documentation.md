# Documentation Skill

## Overview

This skill covers creating and maintaining documentation for all features and components in UK English.

## Prerequisites

- Excellent UK English writing skills
- Technical documentation experience
- Understanding of Markdown format
- Knowledge of the project structure
- Ability to explain complex concepts clearly

## Documentation Types

### Feature Documentation

Every feature must have comprehensive documentation including:

- Overview and purpose
- User requirements
- Technical implementation
- API endpoints (if applicable)
- Database schema changes
- Testing approach
- Usage examples

### API Documentation

- Endpoint descriptions
- Request/response formats
- Authentication requirements
- Error codes and messages
- Rate limiting information
- Example requests and responses

### Component Documentation

- Component purpose and usage
- Props and events documentation
- Styling guidelines
- Accessibility features
- Examples and use cases
- Troubleshooting common issues

## Documentation Structure

### Feature Template

```markdown
# Feature Name

## Overview

Brief description of the feature and its purpose.

## Requirements

- User requirement 1
- User requirement 2
- Technical requirement 1

## Implementation

### Backend Changes

- Model changes
- Enum definitions
- Service classes (core business logic)
- FormRequest classes (validation)
- API resources (responses)
- API endpoints
- Database migrations

### Frontend Changes

- Components created/modified
- Pages affected
- State management
- User interface

## Database Schema

Describe any database changes with table structures.

## API Documentation

Document all new or modified API endpoints.

## Testing

- Unit tests written
- Feature tests written
- E2E tests written
- Coverage achieved

## Usage Examples

Provide practical examples of how to use the feature.

## Troubleshooting

Common issues and their solutions.
```

### API Documentation Template

```markdown
# API Endpoint: [Method] [Path]

## Description

Detailed description of what this endpoint does.

## Authentication

Describe authentication requirements.

## Request

### Headers
```

Content-Type: application/json
Authorization: Bearer {token}

````

### Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| param1 | string | Yes | Description of param1 |

### Body
```json
{
  "field1": "value1",
  "field2": "value2"
}
````

## Response

### Success Response (200)

```json
{
    "data": {
        "field1": "value1"
    }
}
```

### Error Responses

#### 400 Bad Request

```json
{
    "error": "Validation failed",
    "messages": {
        "field1": ["Field 1 is required"]
    }
}
```

## Examples

Provide curl or JavaScript examples.

```

## Quality Checklist
Before completing documentation:
- [ ] Content is in UK English
- [ ] All technical details are accurate
- [ ] Examples are tested and working
- [ ] Structure follows templates
- [ ] Grammar and spelling are correct
- [ ] Code blocks are properly formatted
- [ ] Links are working

## Writing Guidelines

### Language Standards
- Use UK English spelling (colour, organise, analyse, etc.)
- Use formal but accessible tone
- Avoid jargon where possible
- Explain technical terms when necessary
- Use active voice where appropriate

### Formatting Standards
- Use proper Markdown formatting
- Use code blocks with language specification
- Use tables for structured data
- Use bullet points for lists
- Use proper heading hierarchy

### Content Standards
- Be accurate and up-to-date
- Provide practical examples
- Include troubleshooting information
- Cover edge cases and error conditions
- Be comprehensive but concise

## File Organisation
```

docs/
├── features/
│ ├── feature-name.md
│ └── another-feature.md
├── api/
│ ├── endpoints/
│ └── authentication.md
├── components/
│ ├── ui-components.md
│ └── forms.md
├── guides/
│ ├── getting-started.md
│ └── deployment.md
└── troubleshooting/
└── common-issues.md

```

## Maintenance
- Update documentation when features change
- Review documentation regularly for accuracy
- Remove outdated information
- Add new documentation for new features
- Ensure all examples remain functional

## Tools and Resources
- Markdown editors with preview
- Grammar checking tools
- Screenshot tools for visual documentation
- API testing tools for endpoint documentation
- Version control for documentation changes

## Review Process
- Technical review for accuracy
- Editorial review for clarity and grammar
- User testing for examples and tutorials
- Regular audits for currency and completeness
```
