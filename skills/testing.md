# Testing Skill

## Overview

This skill covers testing strategies for both backend and frontend components using PestPHP, Vitest, and Cypress.

## Prerequisites

- PestPHP 4.x for backend testing
- Vitest for frontend unit testing
- Cypress for E2E testing
- Understanding of test-driven development
- Knowledge of testing best practices

## Backend Testing with PestPHP

### Unit Tests

- Test business logic in service classes
- Mock external dependencies
- Test FormRequest validation rules
- Test API resource transformations
- Test enum functionality
- Test edge cases and error conditions
- Use proper test data factories
- Aim for high code coverage

### Feature Tests

- Test complete user workflows
- Test API endpoints with FormRequest validation
- Test authentication and authorisation
- Test form validation through FormRequest classes
- Test database interactions
- Test API resource responses

### Test Structure

```php
// tests/Unit/ExampleTest.php
test('example unit test', function () {
    // Arrange
    $input = 'test';

    // Act
    $result = someFunction($input);

    // Assert
    expect($result)->toBe('expected');
});

// tests/Feature/ExampleTest.php
test('example feature test', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)
        ->get('/api/endpoint');

    // Assert
    $response->assertStatus(200);
});
```

## Frontend Testing with Vitest

### Component Testing

- Test component rendering
- Test user interactions
- Test props and emits
- Test computed properties
- Test error states

### Composable Testing

- Test reusable logic
- Test reactive state
- Test side effects
- Test error handling

### Test Structure

```typescript
// tests/components/ExampleComponent.test.ts
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ExampleComponent from '@/Components/ExampleComponent.vue';

describe('ExampleComponent', () => {
    it('renders correctly', () => {
        const wrapper = mount(ExampleComponent, {
            props: {
                title: 'Test Title',
            },
        });

        expect(wrapper.text()).toContain('Test Title');
    });
});
```

## E2E Testing with Cypress

### User Workflow Testing

- Test complete user journeys
- Test authentication flows
- Test form submissions
- Test navigation
- Test responsive design

### Test Structure

```typescript
// cypress/e2e/example.cy.ts
describe('Example Workflow', () => {
    it('completes user journey', () => {
        cy.visit('/');
        cy.get('[data-testid="login-button"]').click();
        cy.get('[data-testid="email-input"]').type('test@example.com');
        cy.get('[data-testid="password-input"]').type('password');
        cy.get('[data-testid="submit-button"]').click();
        cy.url().should('include', '/dashboard');
    });
});
```

## Quality Checklist

Before completing any testing task:

- [ ] All tests pass
- [ ] Test coverage is 80%+
- [ ] Tests are well-structured and readable
- [ ] Tests cover edge cases
- [ ] Tests are maintainable
- [ ] No flaky tests

## Testing Best Practices

### General Principles

- Write tests before writing code (TDD)
- Test one thing per test
- Use descriptive test names
- Use proper setup and teardown
- Mock external dependencies

### Backend Testing

- Use factories for test data
- Test database transactions
- Test validation rules
- Test error handling
- Test performance-critical code

### Frontend Testing

- Test component states
- Test user interactions
- Test form validation
- Test error boundaries
- Test accessibility

## Test Data Management

- Use factories for consistent test data
- Clean up test data after each test
- Use proper database transactions
- Use seeders for complex test scenarios
- Ensure test isolation

## Continuous Integration

- Run tests on every commit
- Use parallel test execution
- Generate coverage reports
- Fail builds on test failures
- Monitor test performance

## Common Patterns

- Arrange-Act-Assert pattern
- Given-When-Then pattern
- Page Object Model for E2E tests
- Custom test helpers and utilities
- Proper test organisation and structure
