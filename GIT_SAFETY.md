# Git Safety Guide - CRITICAL INFORMATION

## ⚠️ WARNING: This Directory Contains WordPress Sites + Laravel App

This repository has a **unique structure**:
- **Laravel application** (tracked by Git)
- **100+ WordPress sites** (NOT tracked, added daily)

**ONE WRONG GIT COMMAND CAN DELETE ALL WORDPRESS SITES AND CUSTOMER DATA!**

---

## 🚫 DANGEROUS COMMANDS - NEVER RUN THESE

### ❌ NEVER EVER RUN:
```bash
git clean -fdx         # DELETES EVERYTHING including ignored files (WordPress sites)
git clean -fdX         # DELETES all ignored files (WordPress sites)
git clean -fx          # DELETES ignored files
git reset --hard       # Dangerous when combined with clean
```

### ❌ DANGEROUS RESET COMMANDS:
```bash
git reset --hard HEAD  # OK alone, but if followed by git clean -fdx = DISASTER
git reset --hard origin/main && git clean -fdx  # WILL DELETE ALL WORDPRESS SITES
```

---

## ✅ SAFE COMMANDS

### Check what would be deleted (ALWAYS run this first):
```bash
git clean -fdn         # DRY RUN - shows what would be deleted without deleting
git status --ignored   # Shows all ignored files (WordPress sites)
```

### Safe cleanup (only removes untracked Laravel files):
```bash
git clean -fd          # Removes ONLY untracked files (respects .gitignore)
```

### Before ANY git clean, ALWAYS:
1. Run dry-run first: `git clean -fdn`
2. Verify it's not listing WordPress sites
3. If uncertain, DON'T run it

---

## 🛡️ Protection Mechanisms in Place

### 1. Whitelist-based .gitignore
The `.gitignore` uses a **whitelist approach**:
- Ignores EVERYTHING at root level with `/*`
- Explicitly whitelists ONLY Laravel directories
- **Result**: All WordPress sites are automatically ignored

### 2. How it works:
```gitignore
# Ignore everything at root
/*

# Whitelist only Laravel app
!/app/
!/config/
!/routes/
# ... etc
```

### 3. Verification:
```bash
# Check if a directory is ignored
git check-ignore -v directoryname

# Should output: .gitignore:34:/*  directoryname
```

---

## 📋 Daily Workflow (Safe Practices)

### When working with Laravel app:
```bash
# 1. Check status
git status

# 2. Stage Laravel files
git add app/ config/ routes/  # Be specific

# 3. Commit
git commit -m "Your message"

# 4. Push
git push origin main
```

### When new WordPress sites are added:
```bash
# Do NOTHING with Git!
# They are automatically ignored
# No need to add to .gitignore individually
```

---

## 🔧 What Happened Before (Incident Analysis)

**Likely scenario that caused data loss:**
```bash
git reset --hard
git clean -fdx     # ← This deleted ALL ignored files including WordPress sites
```

**Why it happened:**
- The `-x` flag tells git clean to remove ignored files
- WordPress sites are in .gitignore (ignored)
- Git clean -x removed them all

---

## 🚨 If You Need to Clean/Reset

### Option 1: Clean only Laravel files
```bash
# See what will be removed
git clean -fdn

# If safe, remove untracked files
git clean -fd
```

### Option 2: Reset Laravel code
```bash
# Reset code changes
git reset --hard HEAD

# DO NOT RUN git clean after this unless absolutely sure
```

### Option 3: Full Laravel reset (nuclear option)
```bash
# Backup WordPress sites first!
tar -czf ~/wordpress-backup-$(date +%Y%m%d-%H%M%S).tar.gz */

# Then reset
git reset --hard origin/main
git clean -fd  # WITHOUT -x flag
```

---

## 📞 Emergency Recovery

If WordPress sites are deleted:
1. **STOP immediately** - don't run more commands
2. Check server backups: `/var/backups/` or Plesk backups
3. Contact system admin
4. WordPress sites might be in `/var/www/vhosts/*/httpdocs/` backup

---

## ✅ Verification Checklist

Before running any git clean:
- [ ] Run `git clean -fdn` first (dry run)
- [ ] Verify output doesn't list WordPress site directories
- [ ] Ensure NO `-x` or `-X` flags
- [ ] When in doubt, ask for help or skip it

---

## 🎯 Quick Reference

| Command | Safe? | What it does |
|---------|-------|-------------|
| `git clean -fdn` | ✅ YES | Dry run - shows what would be deleted |
| `git clean -fd` | ✅ YES | Removes untracked files (respects .gitignore) |
| `git clean -fdx` | ❌ NO | Removes everything including WordPress sites |
| `git clean -fdX` | ❌ NO | Removes only ignored files (WordPress sites) |
| `git status` | ✅ YES | Always safe to check status |
| `git reset --hard` | ⚠️ CAUTION | Safe alone, dangerous if followed by clean -x |

---

## 💡 Remember

1. **WordPress sites are NOT in Git** - they're ignored on purpose
2. **Never use -x or -X flags** with git clean
3. **Always dry-run first** with -n flag
4. **The .gitignore protects you** - don't override it
5. **When in doubt, don't run it** - ask first

---

**Last Updated:** 2024-12-16
**Created after:** Data loss incident to prevent future occurrences
