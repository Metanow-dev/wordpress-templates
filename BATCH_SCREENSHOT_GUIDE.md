# Batch Screenshot Capture - Quick Start Guide

This guide shows you how to recapture screenshots for multiple templates at once, perfect for fixing 503 errors or updating specific sites.

## Quick Reference

```bash
# From comma-separated list
php artisan templates:batch-screenshot --slugs=site1,site2,site3 --force

# From text file
php artisan templates:batch-screenshot --file=failed_slugs.txt --force

# From CSV file
php artisan templates:batch-screenshot --file=export.csv --force

# With specific CSV column
php artisan templates:batch-screenshot --file=export.csv --column=slug --force
```

## Your Use Case: Fix 503 Error Screenshots

### Step 1: Create a List of Problem Sites

**Option A: Manual List (if you know the slugs)**

Create a text file with one slug per line:

```bash
cat > 503_errors.txt << EOF
template-with-503
another-problematic-site
slow-loading-template
timeout-template
EOF
```

**Option B: Export from Database**

If you want to find templates automatically:

```bash
# Connect to database and export
mysql -u your_user -p your_database -e \
  "SELECT slug FROM templates WHERE screenshot_url IS NULL OR screenshot_url LIKE '%error%'" \
  > problem_slugs.txt

# Clean up the output
tail -n +2 problem_slugs.txt | tr -d '\t' > clean_slugs.txt
```

**Option C: Create CSV in Excel/Sheets**

| slug | issue | priority |
|------|-------|----------|
| template1 | 503 error | high |
| template2 | timeout | high |
| template3 | 503 error | medium |

Save as `problems.csv`

### Step 2: Run Batch Recapture

**For Text File:**
```bash
php artisan templates:batch-screenshot \
  --file=503_errors.txt \
  --force \
  --delay=5 \
  --continue-on-error
```

**For CSV File:**
```bash
php artisan templates:batch-screenshot \
  --file=problems.csv \
  --column=slug \
  --force \
  --delay=5 \
  --continue-on-error
```

**For Just a Few Sites:**
```bash
php artisan templates:batch-screenshot \
  --slugs=site1,site2,site3 \
  --force
```

### Step 3: Review Results

The command will show:
- Progress for each template (X/Y)
- Success/failure status
- Summary with success count
- List of failed slugs for retry

Example output:
```
Batch Screenshot Capture
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total templates: 5
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[1/5] Processing: template1
  ✓ Success
  Waiting 5s before next capture...

[2/5] Processing: template2
  ✓ Success
  ...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Batch Processing Complete
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total processed:  5
Successful:       4
Failed:           1

Failed Slugs (for retry):
  • template5

To retry failed slugs, use:
  php artisan templates:batch-screenshot --slugs=template5 --force
```

### Step 4: Retry Failed Captures (if any)

If some captures failed, copy the suggested retry command:

```bash
php artisan templates:batch-screenshot --slugs=template5 --force --delay=10
```

Or increase the delay and try again:

```bash
php artisan templates:batch-screenshot \
  --slugs=failed-slug \
  --force \
  --delay=10 \
  --w=1920 \
  --h=1080
```

## Command Options Explained

| Option | Description | Example |
|--------|-------------|---------|
| `--slugs=` | Comma-separated list | `--slugs=site1,site2,site3` |
| `--file=` | Read from text or CSV file | `--file=slugs.txt` |
| `--column=` | CSV column name or index | `--column=slug` or `--column=1` |
| `--force` | Recapture even if exists | Required for 503 error fixes |
| `--delay=3` | Seconds between captures | `--delay=5` for slower server |
| `--continue-on-error` | Don't stop on failure | Recommended for large batches |
| `--fullpage` | Capture full page | For landing pages |
| `--w=1200` | Viewport width | `--w=1920` for HD |
| `--h=800` | Viewport height | `--h=1080` for HD |

## File Format Examples

### Text File (slugs.txt)
```
template-one
template-two
template-three
# Comments are ignored
template-four
```

### CSV File (templates.csv)
```csv
slug,status,issue
template-one,failed,503 error
template-two,failed,timeout
template-three,outdated,needs update
```

### CSV with Different Column Names
```csv
id,template_slug,error_code
1,my-site,503
2,another-site,timeout
3,third-site,503
```

Use: `--file=data.csv --column=template_slug`

## Common Scenarios

### Scenario 1: A Few Known Problem Sites
```bash
# Quick fix for 3-4 sites
php artisan templates:batch-screenshot --slugs=site1,site2,site3,site4 --force
```

### Scenario 2: List from Visual Inspection
```bash
# You noticed these have 503 errors
cat > fix_these.txt << EOF
ecommerce-store
fitness-pro
restaurant-deluxe
portfolio-creative
EOF

php artisan templates:batch-screenshot --file=fix_these.txt --force --delay=5
```

### Scenario 3: Export from Database Query
```bash
# Find all without screenshots
mysql -u user -p db -N -e "SELECT slug FROM templates WHERE screenshot_url IS NULL" > needs_capture.txt

# Batch capture
php artisan templates:batch-screenshot --file=needs_capture.txt --force --continue-on-error
```

### Scenario 4: Spreadsheet Tracking
```bash
# Export tracking sheet as CSV
# File: maintenance.csv with columns: slug, issue, priority

php artisan templates:batch-screenshot \
  --file=maintenance.csv \
  --column=slug \
  --force \
  --delay=5
```

## Tips for Success

1. **Start Small**: Test with 2-3 templates first
   ```bash
   php artisan templates:batch-screenshot --slugs=test1,test2 --force
   ```

2. **Use Longer Delays on Shared Hosting**: Prevents 503 errors
   ```bash
   --delay=10  # 10 seconds between captures
   ```

3. **Continue on Error for Large Batches**: Don't stop if one fails
   ```bash
   --continue-on-error
   ```

4. **Save Output for Large Batches**: Keep a log
   ```bash
   php artisan templates:batch-screenshot --file=large_list.txt --force > batch_log.txt 2>&1
   ```

5. **Run During Low-Traffic Hours**: Less server load
   ```bash
   # Schedule for 2 AM
   0 2 * * * cd /path/to/app && php artisan templates:batch-screenshot --file=weekly.txt --force
   ```

## Troubleshooting

### Problem: Command not found
**Solution**: Make sure you're in the project directory
```bash
cd /var/www/vhosts/megasandboxs.com/httpdocs
php artisan templates:batch-screenshot --help
```

### Problem: All captures still failing
**Solution**: Increase delay and check server resources
```bash
# Use conservative settings
php artisan templates:batch-screenshot \
  --file=slugs.txt \
  --force \
  --delay=10 \
  --w=1200 \
  --h=800
```

### Problem: Some succeed, some fail
**Solution**: Retry failed ones with increased delay
```bash
# Command will suggest retry command at the end
# Or manually:
php artisan templates:batch-screenshot --slugs=failed-slug --force --delay=15
```

### Problem: CSV not reading correct column
**Solution**: Specify column by name or index
```bash
# By name (case-insensitive)
--column=slug

# By index (0-based: 0=first, 1=second, etc.)
--column=1
```

### Problem: Process taking too long
**Solution**: Process in smaller batches
```bash
# Split your file
split -l 10 large_list.txt batch_

# Process each batch
php artisan templates:batch-screenshot --file=batch_aa --force
# Wait, then:
php artisan templates:batch-screenshot --file=batch_ab --force
```

## Production Server Commands

On your Plesk server (`megasandboxs.com`), use the artisan wrapper:

```bash
# Change to app directory
cd /var/www/vhosts/megasandboxs.com/httpdocs

# Use php artisan directly (or your wrapper if available)
php artisan templates:batch-screenshot --file=503_errors.txt --force --delay=5
```

## Integration with Existing Workflow

This command complements your existing commands:

```bash
# Regular workflow
php artisan templates:scan                    # Find all templates
php artisan templates:screenshot --new-only   # Capture new ones

# When you notice 503 errors
php artisan templates:batch-screenshot --file=problems.txt --force

# Fix permissions after
php artisan templates:fix-permissions
```

## Need More Help?

- See [MAINTENANCE.md](MAINTENANCE.md) for detailed documentation
- See [README.md](README.md) for general screenshot commands
- Check Laravel logs: `tail -f storage/logs/laravel.log`
