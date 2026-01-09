#!/bin/bash

# Vaulty Setup Script
# This script will set up Vaulty on your system

set -e

echo "🚀 Setting up Vaulty..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker and Docker Compose first."
    echo "Visit: https://docs.docker.com/get-docker/"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose."
    exit 1
fi

# Check if docker daemon is running
if ! docker info &> /dev/null; then
    echo "❌ Docker daemon is not running. Please start Docker."
    exit 1
fi

echo "✅ Docker is installed and running"

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p storage/logs
mkdir -p storage/uploads
mkdir -p src/src/Config
mkdir -p src/src/Controllers
mkdir -p src/src/Models
mkdir -p src/src/Services
mkdir -p src/src/Middleware
mkdir -p src/src/Utils
mkdir -p src/public
mkdir -p docker/nginx/conf.d

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod -R 755 src

# Check if .env file exists
if [ ! -f "src/.env" ]; then
    echo "📝 Creating .env file from template..."
    cp src/.env.example src/.env
    echo "⚠️  Please edit src/.env with your custom configuration before starting."
fi

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose build

echo "🚀 Starting services..."
docker-compose up -d

echo "⏳ Waiting for services to initialize..."
sleep 10

# Check if services are running
echo "🔍 Checking services status..."
docker-compose ps

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
MAX_WAIT=60
WAIT_COUNT=0
until docker-compose exec -T db mysql -h localhost -P 3307 -u vaulty_user -pvaulty_pass -e "SELECT 1" vaulty &> /dev/null; do
    echo "   Waiting for MySQL... ($((WAIT_COUNT))s)"
    WAIT_COUNT=$((WAIT_COUNT + 2))
    if [ $WAIT_COUNT -gt $MAX_WAIT ]; then
        echo "❌ MySQL failed to start within $MAX_WAIT seconds"
        echo "Check logs with: docker-compose logs db"
        exit 1
    fi
    sleep 2
done

echo "✅ MySQL is ready!"

# Show access information
echo ""
echo "🎉 Vaulty is ready!"
echo ""
echo "📱 Access Points:"
echo "   Dashboard:    http://localhost:8080"
echo "   API:          http://localhost:8080/api"
echo "   phpMyAdmin:   http://localhost:8081"
echo ""
echo "🔐 Default Credentials:"
echo "   Username: admin"
echo "   Password: admin123"
echo ""
echo "⚠️  IMPORTANT: Change the default password immediately after first login!"
echo ""
echo "📚 Documentation: See README.md for full API documentation"
echo ""
echo "🔧 Useful Commands:"
echo "   View logs:        docker-compose logs -f"
echo "   Stop services:    docker-compose down"
echo "   Restart services: docker-compose restart"
echo "   Access MySQL:     docker-compose exec db mysql -u vaulty_user -p vaulty"
echo ""
echo "🚀 Happy file managing with Vaulty!"