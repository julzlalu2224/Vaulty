# MySQL Workbench Guide for Vaulty

This guide explains how to use the MySQL Workbench script for Vaulty database management.

## 📋 Script Overview

The `mysql-workbench-script.sql` file contains:

- **Database setup** with proper character sets
- **Table creation** with indexes and constraints
- **Sample data** insertion
- **Helper views** for easy querying
- **Useful queries** for common tasks
- **Maintenance** procedures
- **Security** checks
- **Analytics** and reporting

## 🚀 Quick Start

### 1. Connect to Database

1. Open MySQL Workbench
2. Create new connection:
   - **Connection Name:** Vaulty
   - **Hostname:** localhost
   - **Port:** 3307 (changed from 3306 to avoid conflicts)
   - **Username:** vaulty_user
   - **Password:** vaulty_pass
   - **Default Schema:** vaulty

### 2. Run the Script

**Option A: Run All at Once**
1. Open `mysql-workbench-script.sql`
2. Press `Ctrl+Shift+Enter` or click ⚡ lightning bolt icon
3. Review output in Results panel

**Option B: Run Section by Section**
1. Select a section (between `-- SECTION X` markers)
2. Press `Ctrl+Enter` or click ▶️ play button
3. Check results before proceeding

## 📖 Script Sections

### Section 1: Database Setup
```sql
-- Creates the vaulty database with proper encoding
-- Run this first if database doesn't exist
```

### Section 2: Table Creation
```sql
-- Creates all tables with indexes and foreign keys
-- Run this to set up the schema
```

### Section 3: Sample Data
```sql
-- Inserts test users, projects, and files
-- Useful for testing and development
```

### Section 4: Helper Views
```sql
-- Creates useful views for monitoring
-- Run once for easier querying
```

### Section 5: Useful Queries
```sql
-- Common queries for daily operations
-- Copy and run individual queries as needed
```

### Section 6: Maintenance
```sql
-- Cleanup and optimization queries
-- Run periodically or as needed
```

### Section 7: Security
```sql
-- Security audit queries
-- Run regularly to monitor access
```

### Section 8: Performance
```sql
-- Database performance monitoring
-- Use to identify bottlenecks
```

### Section 9: Backup/Restore
```sql
-- Commands for backup operations
-- Run in terminal, not Workbench
```

### Section 10: Troubleshooting
```sql
-- Find data inconsistencies
-- Run when issues occur
```

### Section 11: Analytics
```sql
-- Usage statistics and reports
-- Run for business insights
```

## 🔧 Common Tasks

### Task 1: Initial Setup
```sql
-- Run these sections in order:
1. Section 1: Database Setup
2. Section 2: Table Creation
3. Section 3: Sample Data (optional)
4. Section 4: Helper Views
```

### Task 2: View User Statistics
```sql
-- Run query from Section 5, Query 1
-- Or select from view:
SELECT * FROM user_stats;
```

### Task 3: Check Storage Usage
```sql
-- Run query from Section 5, Query 3
-- Or:
SELECT * FROM project_stats;
```

### Task 4: Find Duplicate Files
```sql
-- Run query from Section 5, Query 2
-- Shows files with same hash
```

### Task 5: Monitor API Keys
```sql
-- Run query from Section 5, Query 6
-- Shows key usage and last activity
```

### Task 6: Clean Up Old Data
```sql
-- Run queries from Section 6:
-- 1. Remove orphaned files
-- 2. Delete expired keys
-- 3. Archive old logs
```

### Task 7: Security Audit
```sql
-- Run queries from Section 7:
-- 1. List admin users
-- 2. Check public files
-- 3. Find inactive keys
```

### Task 8: Performance Check
```sql
-- Run queries from Section 8:
-- 1. Table sizes
-- 2. Index usage
-- 3. Query performance
```

## 📊 Using Views

### User Statistics View
```sql
SELECT * FROM user_stats;
SELECT * FROM user_stats WHERE role = 'admin';
SELECT username, project_count, total_storage_bytes 
FROM user_stats 
ORDER BY total_storage_bytes DESC;
```

### Project Statistics View
```sql
SELECT * FROM project_stats;
SELECT * FROM project_stats WHERE is_public = TRUE;
SELECT owner_name, SUM(total_storage_bytes) as total 
FROM project_stats 
GROUP BY owner_name;
```

### File Search View
```sql
SELECT * FROM file_search;
SELECT * FROM file_search WHERE mime_type LIKE 'image/%';
SELECT * FROM file_search WHERE metadata LIKE '%important%';
```

### Recent Activity View
```sql
SELECT * FROM recent_activity;
SELECT * FROM recent_activity WHERE activity_type = 'file_upload';
```

## 🎯 Advanced Queries

### Storage Growth Analysis
```sql
-- Monthly storage growth
SELECT * FROM recent_activity WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### User Activity Timeline
```sql
-- Run Section 5, Query 10
-- Shows daily activity for last 7 days
```

### File Type Distribution
```sql
-- Run Section 11, Query 3
-- Shows which file types are most common
```

### Large Files Report
```sql
-- Run Section 5, Query 8
-- Finds files larger than 10MB
```

## 🛡️ Security Monitoring

### Check for Issues
```sql
-- Run Section 10 to find:
-- 1. Orphaned files
-- 2. Invalid paths
-- 3. Duplicate filenames
```

### Audit Trail
```sql
-- View recent actions
SELECT * FROM audit_log 
ORDER BY created_at DESC 
LIMIT 20;

-- Count actions by type
SELECT action, COUNT(*) as count 
FROM audit_log 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY action;
```

## 📈 Performance Optimization

### Check Table Sizes
```sql
-- Run Section 8, Query 1
-- Identify large tables
```

### Index Analysis
```sql
-- Run Section 8, Query 2
-- Verify indexes are being used
```

### Query Performance
```sql
-- Run Section 8, Query 3
-- Requires slow query log enabled
```

## 🧹 Maintenance Tasks

### Weekly Cleanup
```sql
-- Run these from Section 6:
1. Remove orphaned files
2. Delete expired API keys
3. Update file hashes
```

### Monthly Archive
```sql
-- Archive old audit logs
-- Run Section 6, Query 3
```

### Quarterly Review
```sql
-- Deactivate unused API keys
-- Run Section 6, Query 5
```

## 🔍 Troubleshooting

### Data Inconsistencies
```sql
-- Run Section 10, Query 1
-- Shows all data integrity issues
```

### Missing References
```sql
-- Run Section 10, Query 1
-- Finds orphaned records
```

### Invalid Paths
```sql
-- Run Section 10, Query 2
-- Finds files with wrong storage paths
```

## 📊 Reporting

### Generate Reports
```sql
-- User activity report
SELECT * FROM user_stats;

-- Project summary
SELECT * FROM project_stats;

-- Storage usage by month
-- Run Section 11, Query 1

-- Top files by downloads
-- Run Section 5, Query 5
```

## 💡 Tips and Tricks

### 1. Save Frequently Used Queries
- Right-click in editor → "Save Script"
- Create folders for different categories

### 2. Use Query Snippets
- Highlight any query
- Press `Ctrl+Shift+M` to execute selection
- Use `Ctrl+/` to comment/uncomment

### 3. Export Results
- Right-click results → "Export"
- Choose format (CSV, JSON, SQL)
- Save for reports

### 4. Schedule Queries
- Use MySQL Event Scheduler
- Automate maintenance tasks

### 5. Monitor in Real-time
- Open multiple query tabs
- Run monitoring queries periodically
- Set up alerts for issues

## ⚠️ Important Notes

### Before Running Scripts
1. **Backup your database** first
2. Test on development environment
3. Review queries before execution
4. Check for destructive operations

### MySQL 8.0 JSON Indexing Fix
The script includes generated columns for JSON indexing:
- `metadata_description` - Extracts description from JSON
- `metadata_tags` - Extracts tags from JSON
- These are automatically updated when metadata changes
- Enables efficient full-text search on JSON fields

**Note:** If you get error 3152, ensure you're using MySQL 8.0+ and the generated columns syntax.

### Production Considerations
1. Run maintenance during off-hours
2. Monitor query performance
3. Use transactions for critical operations
4. Keep audit logs for compliance

### Security Best Practices
1. Don't store passwords in scripts
2. Use parameterized queries
3. Limit admin access
4. Regular security audits

## 🆘 Troubleshooting Guide

### Connection Issues
```
Error: Can't connect to MySQL server
Solution: Check if Docker containers are running
Command: docker-compose ps
```

### Permission Issues
```
Error: Access denied for user
Solution: Verify credentials in src/.env
```

### Table Already Exists
```
Error: Table 'users' already exists
Solution: Use "CREATE TABLE IF NOT EXISTS"
```

### Foreign Key Errors
```
Error: Cannot add or update a child row
Solution: Run queries in correct order
```

## 📞 Support

### Getting Help
1. Check Vaulty documentation
2. Review Docker logs: `docker-compose logs db`
3. Test connection with: `docker-compose exec db mysql -u vaulty_user -p vaulty`

### Common Issues
- **Port conflicts:** Change port in docker-compose.yml
- **Permission denied:** Check file permissions
- **Out of memory:** Increase Docker memory allocation

## 🎯 Quick Reference

### Key Queries to Remember
```sql
-- User stats: SELECT * FROM user_stats;
-- Project stats: SELECT * FROM project_stats;
-- File search: SELECT * FROM file_search;
-- Recent activity: SELECT * FROM recent_activity;
-- Table sizes: Run Section 8, Query 1
-- Security audit: Run Section 7
```

### Maintenance Schedule
- **Daily:** Monitor audit logs
- **Weekly:** Clean up orphaned data
- **Monthly:** Archive old logs
- **Quarterly:** Review API keys

---

**Remember:** Always backup before running maintenance queries!