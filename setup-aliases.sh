#!/bin/bash

# PTO Tracker Alias Setup Script
# This script helps you add the PTO tracker aliases to your shell profile

echo "🚀 Setting up PTO Tracker Docker aliases..."

# Determine shell profile
if [[ "$SHELL" == *"zsh"* ]]; then
    PROFILE="$HOME/.zshrc"
    SHELL_NAME="zsh"
elif [[ "$SHELL" == *"bash"* ]]; then
    PROFILE="$HOME/.bashrc"
    SHELL_NAME="bash"
else
    echo "❌ Unsupported shell: $SHELL"
    echo "Please manually add 'source $(pwd)/pto-aliases' to your shell profile"
    exit 1
fi

# Check if aliases are already added
if grep -q "pto-aliases" "$PROFILE"; then
    echo "✅ Aliases already found in $PROFILE"
    echo "You can now use the aliases! Try: docstatus"
else
    # Add the source line to the profile
    echo "" >> "$PROFILE"
    echo "# PTO Tracker Docker Aliases" >> "$PROFILE"
    echo "source $(pwd)/pto-aliases" >> "$PROFILE"
    
    echo "✅ Added aliases to $PROFILE"
    echo ""
    echo "🔄 To use the aliases in your current session, run:"
    echo "   source $PROFILE"
    echo ""
    echo "🔄 Or restart your terminal"
fi

echo ""
echo "📋 Available aliases:"
echo "  docphpunit     - Run tests"
echo "  docart         - Laravel artisan commands"
echo "  docbuild       - Build assets"
echo "  docwatch       - Watch assets for changes"
echo "  docshell       - Access PHP container shell"
echo "  docstatus      - Show container status"
echo "  docup/down     - Start/stop containers"
echo ""
echo "💡 Examples:"
echo "  docart migrate"
echo "  docphpunit --filter testMethodName"
echo "  docbuild"
echo "  docshell" 