# Vue.js Frontend Development Skill

## Overview

This skill covers Vue.js frontend development tasks including component creation, state management, and user interface development.

## Prerequisites

- Vue.js 3.x with Composition API knowledge
- TypeScript strict mode (TypeScript preferred over JavaScript)
- Inertia.js understanding
- Tailwind CSS for styling
- Bun as package manager

## Task Types

### Component Development

- Use Vue 3 Composition API
- Create reusable components in `resources/js/Components/`
- Follow TypeScript best practices with strict mode
- Use proper props and emits typing
- Implement proper error boundaries

### State Management

- Use Pinia for state management
- Create stores for complex state logic
- Follow TypeScript best practices for store typing
- Implement proper reactivity patterns

### Styling

- Use Tailwind CSS for all styling
- Follow utility-first approach
- Create reusable component classes when appropriate
- Ensure responsive design
- Follow accessibility best practices

### Form Handling

- Use proper form validation
- Implement controlled components
- Handle form submission with proper loading states
- Display appropriate error messages in UK English

## Quality Checklist

Before completing any frontend task:

- [ ] Code passes `bun lint`
- [ ] Code is formatted with `bun format`
- [ ] TypeScript compilation succeeds
- [ ] Components are properly typed
- [ ] No console errors
- [ ] Responsive design works
- [ ] Accessibility standards met

## File Structure

```
resources/
├── js/
│   ├── Components/
│   │   ├── Forms/
│   │   ├── Layout/
│   │   └── UI/
│   ├── Pages/
│   ├── Stores/
│   ├── Types/              # TypeScript type definitions
│   ├── Composables/        # Reusable composition logic
│   └── app.ts
└── css/
    └── app.css
```

resources/
├── js/
│ ├── Components/
│ │ ├── Forms/
│ │ ├── Layout/
│ │ └── UI/
│ ├── Pages/
│ ├── Stores/
│ ├── Types/
│ └── app.ts
└── css/
└── app.css

````

## Example Commands

```bash
# Install dependencies
bun install

# Run development server
bun run dev

# Lint code
bun lint

# Format code
bun format

# Build for production
bun run build
````

## Component Patterns

- Use `<script setup lang="ts">` syntax
- Define props with proper TypeScript interfaces
- Use composables for reusable logic
- Implement proper loading and error states
- Follow Vue 3 best practices

## TypeScript Standards

- **TypeScript Preferred**: Always use TypeScript over JavaScript
- Use strict mode
- Define proper interfaces for all data structures
- Avoid `any` type
- Use proper generic typing
- Implement proper type guards
- Create reusable type definitions in dedicated files

## Inertia.js Integration

- Use proper Inertia page types
- Handle form submissions with Inertia forms
- Implement proper navigation patterns
- Use proper head management for SEO

## Testing

- Write unit tests with Vitest
- Write E2E tests with Cypress
- Test component interactions
- Test form validation
- Test error states

## Common Patterns

- Use composables for reusable logic
- Implement proper error handling
- Use proper loading states
- Follow accessibility guidelines
- Use UK English for all user-facing text
