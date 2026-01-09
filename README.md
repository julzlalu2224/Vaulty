# Vaulty - Self-Hosted File Storage & Management API

![Vaulty](https://img.shields.io/badge/Vaulty-File%20Storage-blue)
![PHP](https://img.shields.io/badge/PHP-8.3-purple)
![Docker](https://img.shields.io/badge/Docker-Ready-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![License](https://img.shields.io/badge/License-MIT-green)

Vaulty is a lightweight, self-hosted file storage and management API built with PHP 8.3+ and Docker. It provides a secure, project-scoped environment for handling file uploads, downloads, and metadata, complete with a modern web dashboard.

## Features

### 🔐 Authentication & Security
- JWT-based authentication
- Role-based access control (Admin/User)
- API key system for programmatic access
- Password hashing with Argon2id
- CORS configuration
- Secure file storage with permissions

### 📁 File Management
- Project-scoped file storage
- File upload with metadata
- File download and streaming
- File search and filtering
- Public file sharing
- File deduplication (SHA256 hashing)
- Large file support (up to 100MB)

### 🎨 Modern Web Dashboard
- Single-page application
- Real-time file browser
- Drag & drop uploads
- Project management
- File metadata editing
- Responsive design
- Dark theme

### 🗄️ Database Structure
- Users table with roles
- Projects table with API keys
- Files table with metadata
- API keys table for programmatic access
- Audit log for security

## Quick Start

### Prerequisites
- Docker & Docker Compose
- Git
- Modern web browser

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/julzlalu2224/Vaulty.git
   cd Vaulty
   ```

2. **Start the services:**
   ```bash
   docker-compose up -d
   ```

3. **Wait for services to initialize** (30-60 seconds)

4. **Access the dashboard:**
   - Dashboard: http://localhost:8080
   - API: http://localhost:8080/api
   - phpMyAdmin: http://localhost:8081

### Default Credentials
- **Username:** `admin`
- **Password:** `admin123`

⚠️ **Change these immediately after first login!**

## API Documentation

### Base URL
```
http://localhost:8080/api
```

### Authentication Endpoints

#### Register
```http
POST /auth/register
Content-Type: application/json

{
  "username": "newuser",
  "email": "user@example.com",
  "password": "securepassword123"
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@vaulty.local",
      "role": "admin"
    }
  }
}
```

#### Get Current User
```http
GET /auth/me
Authorization: Bearer <token>
```

#### Refresh Token
```http
POST /auth/refresh
Authorization: Bearer <token>
```

### Project Endpoints

#### Create Project
```http
POST /projects
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "My Project",
  "description": "Project description",
  "is_public": false
}
```

#### List Projects
```http
GET /projects
Authorization: Bearer <token>
```

#### Get Project
```http
GET /projects/{id}
Authorization: Bearer <token>
```

#### Update Project
```http
PUT /projects/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Updated Name",
  "description": "Updated description",
  "is_public": true
}
```

#### Delete Project
```http
DELETE /projects/{id}
Authorization: Bearer <token>
```

### File Endpoints

#### Upload File
```http
POST /files
Authorization: Bearer <token>
Content-Type: multipart/form-data

file: (binary file)
project_id: 1
metadata: {"description": "My file", "tags": ["work", "important"]}
```

Or with API Key:
```http
POST /files
X-API-Key: <project_api_key>
Content-Type: multipart/form-data

file: (binary file)
metadata: {"description": "My file"}
```

#### Download File
```http
GET /files/{id}
Authorization: Bearer <token>
```

Or with API Key:
```http
GET /files/{id}
X-API-Key: <project_api_key>
```

#### List Files in Project
```http
GET /files/project/{projectId}?limit=50&offset=0
Authorization: Bearer <token>
```

#### Search Files
```http
GET /files/search/{projectId}?q=searchterm
Authorization: Bearer <token>
```

#### Public File Access
```http
GET /public/{filename}
```

#### Update File Metadata
```http
PUT /files/{id}/metadata
Authorization: Bearer <token>
Content-Type: application/json

{
  "metadata": {"description": "Updated", "tags": ["new"]}
}
```

#### Delete File
```http
DELETE /files/{id}
Authorization: Bearer <token>
```

## Web Dashboard

The dashboard provides a user-friendly interface for managing files and projects:

### Features
- **Login/Register:** User authentication
- **Dashboard:** Overview of recent files and projects
- **Projects:** Create, manage, and delete projects
- **File Browser:** View and search files within projects
- **Upload:** Drag & drop file uploads with metadata
- **File Details:** View file information and metadata
- **API Key Management:** Copy API keys for programmatic access

### Access
Navigate to `http://localhost:8080` in your browser.

## Docker Architecture

### Services
- **app:** PHP 8.4-FPM application
- **web:** Nginx web server
- **db:** MySQL 8.0 database
- **redis:** Redis for caching (optional)
- **phpmyadmin:** Database management interface

### Volumes
- `vaulty-data`: MySQL persistent storage
- `vaulty-redis`: Redis persistent storage
- `./src`: Application source code
- `./storage`: File storage

### Network
All services communicate via `vaulty-network` bridge network.

## Configuration

### Environment Variables
Create `.env` file in `src/` directory:

```env
# Database
DB_HOST=db
DB_PORT=3306
DB_NAME=vaulty
DB_USER=vaulty_user
DB_PASS=vaulty_pass

# JWT
JWT_SECRET=your_super_secret_key
JWT_EXPIRY=3600

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080

# File Upload
UPLOAD_DIR=/var/www/storage/uploads
MAX_FILE_SIZE=104857600
```

### Security Considerations

1. **Change Default Passwords:**
   - MySQL root password
   - Admin user password
   - JWT secret key

2. **Production Settings:**
   - Set `APP_DEBUG=false`
   - Use strong JWT secret
   - Enable HTTPS
   - Configure proper CORS origins

3. **File Permissions:**
   - Storage directory: 775
   - Uploaded files: 664

4. **Database Security:**
   - Change default database credentials
   - Use strong passwords
   - Limit network access

## Development

### Local Development Setup

1. **Install dependencies:**
   ```bash
   cd src
   composer install
   ```

2. **Start services:**
   ```bash
   docker-compose up -d
   ```

3. **Watch logs:**
   ```bash
   docker-compose logs -f
   ```

4. **Stop services:**
   ```bash
   docker-compose down
   ```

### File Structure
```
Vaulty/
├── docker/
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   ├── mysql/
│   │   ├── init/
│   │   │   └── init.sql
│   │   └── my.cnf
│   └── nginx/
│       ├── nginx.conf
│       └── conf.d/
│           └── default.conf
├── src/
│   ├── src/
│   │   ├── Config/
│   │   │   └── Database.php
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProjectController.php
│   │   │   └── FileController.php
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   ├── Project.php
│   │   │   └── File.php
│   │   ├── Services/
│   │   │   ├── AuthService.php
│   │   │   └── FileService.php
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php
│   │   │   └── ApiKeyMiddleware.php
│   │   ├── Utils/
│   │   │   ├── Response.php
│   │   │   └── Request.php
│   │   └── Router.php
│   ├── public/
│   │   ├── index.php
│   │   └── .htaccess
│   ├── composer.json
│   ├── .env
│   └── .env.example
├── public/
│   └── index.html (Dashboard)
├── storage/
│   ├── logs/
│   └── uploads/
├── docker-compose.yml
└── README.md
```

## Troubleshooting

### Common Issues

**1. Database Connection Error**
```bash
# Check if MySQL is running
docker-compose ps

# View logs
docker-compose logs db

# Wait 30-60 seconds for initialization
```

**2. File Upload Fails**
```bash
# Check storage permissions
docker-compose exec app chmod -R 775 /var/www/storage

# Check upload directory exists
docker-compose exec app ls -la /var/www/storage/uploads
```

**3. API Returns 404**
```bash
# Check nginx configuration
docker-compose exec web nginx -t

# Restart nginx
docker-compose restart web
```

**4. CORS Issues**
```bash
# Check environment variables
docker-compose exec app env | grep CORS

# Ensure proper headers in nginx config
```

### Debug Mode
Enable debug mode in `.env`:
```env
APP_DEBUG=true
```

View application logs:
```bash
docker-compose logs app
```

## API Examples

### cURL Examples

**Login:**
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

**Upload File:**
```bash
curl -X POST http://localhost:8080/api/files \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/file.pdf" \
  -F "project_id=1" \
  -F 'metadata={"description":"My file"}'
```

**List Files:**
```bash
curl -X GET http://localhost:8080/api/files/project/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### JavaScript Example

```javascript
// Login
const login = async (username, password) => {
  const response = await fetch('http://localhost:8080/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });
  return response.json();
};

// Upload file
const uploadFile = async (token, projectId, file, metadata = {}) => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('project_id', projectId);
  formData.append('metadata', JSON.stringify(metadata));

  const response = await fetch('http://localhost:8080/api/files', {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  });
  return response.json();
};
```

## Performance Optimization

### File Storage
- Files are stored in `/var/www/storage/uploads`
- SHA256 hashing for deduplication
- Metadata stored in JSON format

### Caching
- Redis integration for session caching
- Query result caching
- File metadata caching

### Database
- Indexed columns for fast queries
- Proper foreign key relationships
- Optimized queries

## Security Features

### Authentication
- JWT tokens with expiration
- Password hashing (Argon2id)
- Role-based access control

### File Security
- Project-scoped access
- Optional public/private file visibility
- File permission checks
- Secure file deletion

### API Security
- API key authentication
- CORS restrictions
- Rate limiting ready
- Input validation

## License

MIT License - feel free to use and modify for your needs.

## Support

For issues and questions:
- Check the troubleshooting section
- Review Docker logs
- Check database connection
- Verify environment variables

## Contributing

Contributions are welcome! Please ensure:
- Code follows PSR-12 standards
- Tests are added for new features
- Documentation is updated
- Docker configuration is maintained

---

**Built with ❤️ using PHP 8.4, Docker, and MySQL**