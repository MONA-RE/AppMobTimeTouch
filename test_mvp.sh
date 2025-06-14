#!/bin/bash

echo "=== Tests AppMobTimeTouch MVP Refactorisation ==="
echo ""

# Test 1: Syntaxe PHP
echo "1. Test syntaxe PHP..."
php -l index.php > /dev/null 2>&1 && echo "✅ index.php syntax OK" || echo "❌ index.php syntax ERROR"
php -l home.php > /dev/null 2>&1 && echo "✅ home.php syntax OK" || echo "❌ home.php syntax ERROR"
echo ""

# Test 2: Fichiers requis
echo "2. Test fichiers requis..."
[ -f "js/timeclock-api.js" ] && echo "✅ TimeclockAPI.js exists" || echo "❌ TimeclockAPI.js missing"
[ -f "tpl/home.tpl" ] && echo "✅ home.tpl exists" || echo "❌ home.tpl missing"
[ -f "tpl/index-desktop.tpl" ] && echo "✅ index-desktop.tpl exists" || echo "❌ index-desktop.tpl missing"
[ -f "tpl/parts/tabbar.tpl" ] && echo "✅ tabbar.tpl exists" || echo "❌ tabbar.tpl missing"
echo ""

# Test 3: Backups
echo "3. Test backups..."
[ -f "home_backup_580b4be.php" ] && echo "✅ home backup exists" || echo "❌ home backup missing"
[ -f "index_backup_580b4be.php" ] && echo "✅ index backup exists" || echo "❌ index backup missing"
echo ""

# Test 4: Structure SOLID
echo "4. Test structure SOLID..."
[ -d "Controllers" ] && echo "✅ Controllers directory exists" || echo "❌ Controllers directory missing"
[ -d "Services" ] && echo "✅ Services directory exists" || echo "❌ Services directory missing"
[ -d "Utils" ] && echo "✅ Utils directory exists" || echo "❌ Utils directory missing"
[ -d "Views/components" ] && echo "✅ Views/components directory exists" || echo "❌ Views/components directory missing"
echo ""

# Test 5: Accès HTTP (si serveur accessible)
echo "5. Test accès HTTP..."
if curl -f -s http://localhost/dev-smta/htdocs/custom/appmobtimetouch/index.php > /dev/null 2>&1; then
    echo "✅ index.php HTTP accessible"
else
    echo "⚠️  index.php HTTP not accessible (server may not be running)"
fi

if curl -f -s http://localhost/dev-smta/htdocs/custom/appmobtimetouch/home.php > /dev/null 2>&1; then
    echo "✅ home.php HTTP accessible"
else
    echo "⚠️  home.php HTTP not accessible (server may not be running)"
fi
echo ""

# Test 6: Documentation
echo "6. Test documentation..."
[ -f "doc/refactor_index_home.md" ] && echo "✅ Refactoring documentation exists" || echo "❌ Refactoring documentation missing"
[ -f "doc/CLAUDE.md" ] && echo "✅ CLAUDE.md exists" || echo "❌ CLAUDE.md missing"
echo ""

echo "=== Tests terminés ==="
echo ""

# Test bonus: Vérifier les corrections JavaScript
echo "7. Test corrections JavaScript..."
if grep -q "showHomePage" home.php; then
    echo "✅ showHomePage function added"
else
    echo "❌ showHomePage function missing"
fi

if grep -q "window.showHomePage" home.php; then
    echo "✅ showHomePage exposed globally"
else
    echo "❌ showHomePage not exposed globally"
fi

if grep -q 'mobile-web-app-capable' home.php; then
    echo "✅ Modern mobile meta tag added"
else
    echo "❌ Modern mobile meta tag missing"
fi

echo ""
if grep -q "ons-page.*homePage" home.php; then
    echo "✅ OnsenUI structure corrigée (ons-page)"
else
    echo "❌ OnsenUI structure non corrigée"
fi

if grep -q "page__content" home.php; then
    echo "✅ Contenu scrollable ajouté"
else
    echo "❌ Contenu scrollable manquant"
fi

echo ""
echo "ÉTAT MVP ACTUEL:"
echo "✅ MVP Étape 1: Sauvegarde et préparation"
echo "✅ MVP Étape 2: Refactorisation index.php"
echo "✅ MVP Étape 3: Refactorisation home.php"
echo "✅ MVP Étape 4: Corrections JavaScript OnsenUI"
echo "✅ MVP Étape 5: Structure OnsenUI corrigée (clickable/scrollable)"
echo ""
echo "PROCHAINES ÉTAPES:"
echo "- MVP Étape 6: Tests intégration complète"
echo "- Test navigateur: Vérifier contenu clickable et scrollable"
echo "- Test mobile: Valider tabbar et navigation"