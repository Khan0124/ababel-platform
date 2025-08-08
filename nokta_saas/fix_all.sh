#!/bin/bash
# NOKTA POS - Complete Fix for Linux/Mac

echo "================================================"
echo "   NOKTA POS - COMPLETE SYSTEM FIX"
echo "================================================"
echo ""

# Step 1: Clean
echo "[1/8] Cleaning project..."
find . -name "*.freezed.dart" -type f -delete
find . -name "*.g.dart" -type f -delete
find . -name "pubspec.lock" -type f -delete
find . -name ".dart_tool" -type d -exec rm -rf {} + 2>/dev/null
find . -name "build" -type d -exec rm -rf {} + 2>/dev/null
flutter clean

# Step 2: Update Flutter
echo "[2/8] Updating Flutter..."
flutter upgrade --force

# Step 3: Clear caches
echo "[3/8] Clearing caches..."
dart pub cache clean --force
flutter pub cache repair

# Step 4: Install Melos
echo "[4/8] Installing Melos..."
dart pub global deactivate melos 2>/dev/null
dart pub global activate melos

# Step 5: Bootstrap
echo "[5/8] Running Melos bootstrap..."
flutter pub get
melos clean
melos bootstrap

# Step 6: Core setup
echo "[6/8] Setting up Core package..."
cd packages/core
flutter pub get
dart pub get
cd ../..

# Step 7: Generate files
echo "[7/8] Generating files..."
cd packages/core
dart run build_runner clean
dart run build_runner build --delete-conflicting-outputs
cd ../..

# Step 8: App dependencies
echo "[8/8] Installing app dependencies..."
for app in pos_app customer_app driver_app admin_panel; do
    echo "Installing $app..."
    cd apps/$app
    flutter clean
    flutter pub get
    cd ../..
done

echo ""
echo "================================================"
echo "   COMPLETE! System fully rebuilt"
echo "================================================"
echo ""
echo "Testing the fix..."
flutter analyze --no-fatal-warnings

echo ""
echo "Next steps:"
echo "  1. cd apps/pos_app && flutter run"
echo "  2. cd apps/customer_app && flutter run"
echo "  3. cd apps/driver_app && flutter run"
echo "  4. cd apps/admin_panel && flutter run"
echo ""
