#!/bin/bash

# GitHub Repository Setup Script
# This script helps set up the GitHub repository for docscanner

echo "🚀 Document Scanner - GitHub Repository Setup"
echo "==========================================="
echo ""

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "❌ Git is not initialized. Please run 'git init' first."
    exit 1
fi

echo "✅ Git repository is initialized"
echo ""

# Check if remote exists
if ! git remote get-url origin > /dev/null 2>&1; then
    echo "📋 Adding remote origin..."
    git remote add origin https://github.com/sdsmith1981/docscanner.git
    echo "✅ Remote origin added"
else
    echo "✅ Remote origin already exists"
fi

echo ""
echo "📝 Repository Details:"
echo "   Repository: sdsmith1981/docscanner"
echo "   URL: https://github.com/sdsmith1981/docscanner"
echo "   Visibility: Public"
echo ""

echo "🔧 To complete setup, you need to:"
echo ""
echo "1. Create the repository on GitHub:"
echo "   - Go to https://github.com/new"
echo "   - Repository name: docscanner"
echo "   - Owner: sdsmith1981"
echo "   - Visibility: Public"
echo "   - Don't initialize with README (we have one)"
echo "   - Click 'Create repository'"
echo ""
echo "2. Push to GitHub:"
echo "   git push -u origin main"
echo ""
echo "   Or if you need authentication:"
echo "   git push -u origin main --token=YOUR_GITHUB_TOKEN"
echo ""
echo "3. Or use GitHub CLI (recommended):"
echo "   brew install gh  # macOS"
echo "   gh auth login"
echo "   gh repo create sdsmith1981/docscanner --public --source=. --remote=origin --push"
echo ""

echo "📊 Current Git Status:"
echo ""
git status --short
echo ""

echo "📋 Latest Commit:"
echo ""
git log --oneline -1
echo ""

echo "🎉 Repository is ready for GitHub deployment!"
echo ""

# Show what would be pushed
echo "📦 Files to be pushed:"
git ls-files | wc -l | xargs echo "Total files:"
echo ""
echo "📁 Main directories:"
git ls-files | cut -d'/' -f1 | sort | uniq -c | sort -nr