# Vaulty Quick Start Guide

Get Vaulty running in 5 minutes!

## Prerequisites
- Docker and Docker Compose installed
- Git
- Web browser

## Step 1: Clone & Start

```bash
# Clone the repository
git clone https://github.com/julzlalu2224/Vaulty.git
cd Vaulty

# Start everything
./setup.sh
```

## Step 2: Access Dashboard

Open your browser and go to: **http://localhost:8080**

## Step 3: Login

Use these default credentials:
- **Username:** `admin`
- **Password:** `admin123`

⚠️ **Change your password immediately!**

## Step 4: Create Your First Project

1. Click "Projects" in the navigation
2. Click "+ New Project"
3. Enter a name and description
4. Click "Create Project"

## Step 5: Upload Files

1. Click "Upload" in the navigation
2. Select your project from the dropdown
3. Drag & drop files or click to select
4. Add optional metadata (JSON format)
5. Click "Upload Files"

## Step 6: Use the API

### Get your API Key
1. Go to "Projects"
2. Click "API Key" next to your project
3. Copy the key

### Example API call
```bash
# Upload a file using your API key
curl -X POST http://localhost:8080/api/files \
  -H "X-API-Key: YOUR_API_KEY" \
  -F "file=@/path/to/your/file.pdf" \
  -F 'metadata={"description":"My file"}'
```

## What's Included?

### Services
- **Dashboard:** http://localhost:8080
- **API:** http://localhost:8080/api
- **phpMyAdmin:** http://localhost:8081

### Default User
- Username: `admin`
- Password: `admin123`

### Default Project
- Name: `Default Project`
- API Key: `sample_api_key_12345`

## Common Commands

```bash
# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Restart services
docker-compose restart

# Access MySQL
docker-compose exec db mysql -u vaulty_user -p vaulty

# Check status
docker-compose ps
```

## Next Steps

1. **Read the full README.md** for detailed documentation
2. **Check API_GUIDE.md** for complete API reference
3. **Change default passwords** for security
4. **Create your own projects** and upload files
5. **Use the API** for programmatic access

## Troubleshooting

### Services won't start
```bash
# Check if ports are available
netstat -tulpn | grep 8080
netstat -tulpn | grep 3306

# Check Docker logs
docker-compose logs
```

### Can't access dashboard
```bash
# Check if web container is running
docker-compose ps

# Restart web service
docker-compose restart web
```

### Database connection issues
```bash
# Wait 30-60 seconds for initialization
# Check MySQL logs
docker-compose logs db

# Verify database is ready
docker-compose exec db mysql -u vaulty_user -p vaulty -e "SHOW DATABASES;"
```

## Security Checklist

- [ ] Changed admin password
- [ ] Changed JWT secret in .env
- [ ] Changed database passwords
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Configured HTTPS
- [ ] Restricted CORS origins
- [ ] Regular backups configured

## Performance Tips

- Use Redis for caching (already configured)
- Monitor storage usage
- Regularly clean up old files
- Use CDN for public files
- Implement rate limiting

## Support

For issues:
1. Check logs: `docker-compose logs`
2. Verify services: `docker-compose ps`
3. Read documentation: README.md
4. Check API guide: API_GUIDE.md

---

**Vaulty is ready to use! Happy file managing! 🚀**