# Vaulty API Guide

Complete guide to using Vaulty's REST API.

## Base URL
```
http://localhost:8080/api
```

## Authentication

All API endpoints (except registration and login) require authentication via:
- **JWT Token** (Authorization header)
- **API Key** (X-API-Key header)

### Getting Started

1. **Register a new user:**
   ```bash
   curl -X POST http://localhost:8080/api/auth/register \
     -H "Content-Type: application/json" \
     -d '{
       "username": "myuser",
       "email": "user@example.com",
       "password": "mypassword123"
     }'
   ```

2. **Login to get token:**
   ```bash
   curl -X POST http://localhost:8080/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{
       "username": "myuser",
       "password": "mypassword123"
     }'
   ```

   Response:
   ```json
   {
     "success": true,
     "message": "Login successful",
     "data": {
       "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
       "user": {
         "id": 2,
         "username": "myuser",
         "email": "user@example.com",
         "role": "user"
       }
     }
   }
   ```

3. **Use the token in subsequent requests:**
   ```bash
   export TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
   ```

## Authentication Endpoints

### Register
```http
POST /auth/register
Content-Type: application/json

{
  "username": "string",
  "email": "string",
  "password": "string"
}

Response: 201 Created
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user_id": 1
  }
}
```

### Login
```http
POST /auth/login
Content-Type: application/json

{
  "username": "string",
  "password": "string"
}

Response: 200 OK
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "jwt_token_here",
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@vaulty.local",
      "role": "admin"
    }
  }
}
```

### Get Current User
```http
GET /auth/me
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "User info",
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@vaulty.local",
    "role": "admin"
  }
}
```

### Refresh Token
```http
POST /auth/refresh
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "Token refreshed",
  "data": {
    "token": "new_jwt_token_here"
  }
}
```

## Project Endpoints

### Create Project
```http
POST /projects
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "My Project",
  "description": "Project description",
  "is_public": false
}

Response: 201 Created
{
  "success": true,
  "message": "Project created successfully",
  "data": {
    "id": 1,
    "name": "My Project",
    "description": "Project description",
    "owner_id": 1,
    "is_public": false,
    "api_key": "abc123...",
    "created_at": "2024-01-09 10:00:00",
    "updated_at": "2024-01-09 10:00:00"
  }
}
```

### List Projects
```http
GET /projects
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "Projects retrieved",
  "data": [
    {
      "id": 1,
      "name": "My Project",
      "description": "Project description",
      "owner_id": 1,
      "is_public": false,
      "api_key": "abc123...",
      "created_at": "2024-01-09 10:00:00",
      "updated_at": "2024-01-09 10:00:00"
    }
  ]
}
```

### Get Project
```http
GET /projects/{id}
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "Project retrieved",
  "data": {
    "id": 1,
    "name": "My Project",
    "description": "Project description",
    "owner_id": 1,
    "is_public": false,
    "api_key": "abc123...",
    "created_at": "2024-01-09 10:00:00",
    "updated_at": "2024-01-09 10:00:00"
  }
}
```

### Update Project
```http
PUT /projects/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Updated Project Name",
  "description": "Updated description",
  "is_public": true
}

Response: 200 OK
{
  "success": true,
  "message": "Project updated successfully"
}
```

### Delete Project
```http
DELETE /projects/{id}
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "Project deleted successfully"
}
```

## File Endpoints

### Upload File
```http
POST /files
Authorization: Bearer <token>
Content-Type: multipart/form-data

Body:
- file: (binary file)
- project_id: 1
- metadata: {"description": "My file", "tags": ["work", "important"]}

Response: 201 Created
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "file_id": 1,
    "filename": "abc123.pdf",
    "original_name": "document.pdf",
    "size": 1024000,
    "mime_type": "application/pdf",
    "storage_path": "/var/www/storage/uploads/abc123.pdf"
  }
}
```

### Upload with API Key
```http
POST /files
X-API-Key: <project_api_key>
Content-Type: multipart/form-data

Body:
- file: (binary file)
- metadata: {"description": "My file"}

Response: 201 Created
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "file_id": 1,
    "filename": "abc123.pdf",
    "original_name": "document.pdf",
    "size": 1024000,
    "mime_type": "application/pdf",
    "storage_path": "/var/www/storage/uploads/abc123.pdf"
  }
}
```

### Download File
```http
GET /files/{id}
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "File retrieved",
  "data": {
    "file": {
      "id": 1,
      "project_id": 1,
      "filename": "abc123.pdf",
      "original_name": "document.pdf",
      "mime_type": "application/pdf",
      "file_size": 1024000,
      "file_hash": "sha256_hash_here",
      "storage_path": "/var/www/storage/uploads/abc123.pdf",
      "is_public": false,
      "uploaded_by": 1,
      "metadata": "{\"description\":\"My file\",\"tags\":[\"work\",\"important\"]}",
      "created_at": "2024-01-09 10:00:00",
      "updated_at": "2024-01-09 10:00:00"
    },
    "content": "base64_encoded_file_content"
  }
}
```

### Download with API Key
```http
GET /files/{id}
X-API-Key: <project_api_key>

Response: 200 OK
{
  "success": true,
  "message": "File retrieved",
  "data": {
    "file": { ... },
    "content": "base64_encoded_file_content"
  }
}
```

### List Files in Project
```http
GET /files/project/{projectId}?limit=50&offset=0
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "Files retrieved",
  "data": {
    "files": [
      {
        "id": 1,
        "project_id": 1,
        "filename": "abc123.pdf",
        "original_name": "document.pdf",
        "mime_type": "application/pdf",
        "file_size": 1024000,
        "file_hash": "sha256_hash_here",
        "storage_path": "/var/www/storage/uploads/abc123.pdf",
        "is_public": false,
        "uploaded_by": 1,
        "metadata": "{\"description\":\"My file\"}",
        "created_at": "2024-01-09 10:00:00",
        "updated_at": "2024-01-09 10:00:00"
      }
    ],
    "count": 1
  }
}
```

### Search Files
```http
GET /files/search/{projectId}?q=searchterm
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "Search results",
  "data": {
    "files": [
      {
        "id": 1,
        "filename": "abc123.pdf",
        "original_name": "document.pdf",
        "mime_type": "application/pdf",
        "file_size": 1024000,
        "created_at": "2024-01-09 10:00:00"
      }
    ],
    "count": 1
  }
}
```

### Public File Access
```http
GET /public/{filename}

Response: 200 OK
{
  "success": true,
  "message": "File retrieved",
  "data": {
    "file": { ... },
    "content": "base64_encoded_file_content"
  }
}
```

### Update File Metadata
```http
PUT /files/{id}/metadata
Authorization: Bearer <token>
Content-Type: application/json

{
  "metadata": {
    "description": "Updated description",
    "tags": ["updated", "important"]
  }
}

Response: 200 OK
{
  "success": true,
  "message": "Metadata updated successfully"
}
```

### Delete File
```http
DELETE /files/{id}
Authorization: Bearer <token>

Response: 200 OK
{
  "success": true,
  "message": "File deleted successfully"
}
```

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "username": "Required field",
    "email": "Invalid email format"
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Missing authentication token"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Access denied"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Bad Request (400)
```json
{
  "success": false,
  "message": "Invalid request"
}
```

## JavaScript Examples

### Login and Upload
```javascript
async function loginAndUpload() {
  // Login
  const loginResponse = await fetch('http://localhost:8080/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      username: 'myuser',
      password: 'mypassword123'
    })
  });
  
  const { data: { token, user } } = await loginResponse.json();
  
  // Create project
  const projectResponse = await fetch('http://localhost:8080/api/projects', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      name: 'My Project',
      description: 'Test project'
    })
  });
  
  const { data: project } = await projectResponse.json();
  
  // Upload file
  const fileInput = document.getElementById('fileInput');
  const file = fileInput.files[0];
  
  const formData = new FormData();
  formData.append('file', file);
  formData.append('project_id', project.id);
  formData.append('metadata', JSON.stringify({ description: 'My file' }));
  
  const uploadResponse = await fetch('http://localhost:8080/api/files', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  const result = await uploadResponse.json();
  console.log('Upload result:', result);
}
```

### Using API Key
```javascript
async function uploadWithApiKey() {
  const apiKey = 'your_project_api_key_here';
  const fileInput = document.getElementById('fileInput');
  const file = fileInput.files[0];
  
  const formData = new FormData();
  formData.append('file', file);
  formData.append('metadata', JSON.stringify({ description: 'API Upload' }));
  
  const response = await fetch('http://localhost:8080/api/files', {
    method: 'POST',
    headers: {
      'X-API-Key': apiKey
    },
    body: formData
  });
  
  const result = await response.json();
  console.log('Upload result:', result);
}
```

## Python Examples

```python
import requests
import json

# Login
def login(username, password):
    response = requests.post(
        'http://localhost:8080/api/auth/login',
        json={'username': username, 'password': password}
    )
    return response.json()

# Upload file
def upload_file(token, project_id, file_path, metadata=None):
    with open(file_path, 'rb') as f:
        files = {'file': f}
        data = {
            'project_id': project_id,
            'metadata': json.dumps(metadata or {})
        }
        
        response = requests.post(
            'http://localhost:8080/api/files',
            headers={'Authorization': f'Bearer {token}'},
            files=files,
            data=data
        )
        return response.json()

# Usage
result = login('myuser', 'mypassword123')
token = result['data']['token']

upload_result = upload_file(
    token, 
    1, 
    '/path/to/file.pdf',
    {'description': 'Important document'}
)
```

## cURL Examples

```bash
# Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"myuser","password":"mypassword123"}'

# Create project
curl -X POST http://localhost:8080/api/projects \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"My Project","description":"Test"}'

# Upload file
curl -X POST http://localhost:8080/api/files \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/path/to/file.pdf" \
  -F "project_id=1" \
  -F 'metadata={"description":"My file"}'

# List files
curl -X GET http://localhost:8080/api/files/project/1 \
  -H "Authorization: Bearer $TOKEN"

# Download file
curl -X GET http://localhost:8080/api/files/1 \
  -H "Authorization: Bearer $TOKEN"
```

## Best Practices

1. **Always use HTTPS in production**
2. **Store tokens securely** (not in localStorage for sensitive apps)
3. **Implement token refresh** before expiration
4. **Validate file types and sizes** client-side
5. **Use API keys for server-to-server communication**
6. **Implement rate limiting** for production
7. **Regularly rotate API keys**
8. **Monitor audit logs** for security

## Rate Limiting (Recommended)

For production, implement rate limiting:
- 100 requests per minute per user
- 1000 requests per hour per API key
- 10 MB file size limit
- 1000 files per project

## Support

For issues or questions, check:
- Application logs: `docker-compose logs app`
- Database logs: `docker-compose logs db`
- Web server logs: `docker-compose logs web`