#!/usr/bin/env bash
set -euo pipefail

echo "Current changes:"
git status --short

echo ""
echo "This script will NOT commit or push automatically."
echo "Suggested next steps:"
echo "git add ."
echo "git commit -m \"Your message\""
echo "git push"