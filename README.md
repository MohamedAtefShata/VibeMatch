# VibeMatch - Music Recommendation System

VibeMatch is a robust Laravel-based music recommendation system that leverages vector embeddings to provide intelligent song recommendations based on user preferences and listening history.

## 🎵 Project Overview

VibeMatch offers sophisticated music recommendation capabilities:

1. **Song-Based Recommendations**: Get personalized song recommendations based on a list of songs you like
2. **Interaction-Based Recommendations**: Discover new music based on your past recommendations and ratings
3. **User Authentication & Authorization**: Secure user management with role-based permissions
4. **Vector Similarity Search**: Fast and accurate recommendation engine using PostgreSQL's pgvector extension

## 🛠️ Technology Stack

- **Backend**: Laravel 12
- **Frontend**: Vue.js with Inertia
- **Database or docker**: PostgreSQL with pgvector extension
- **Authentication**: Laravel's built-in authentication system
- **Vector Embeddings**: OpenAI API for generating embeddings

## 🏗️ Architecture & Design Patterns

The application follows several design patterns to maintain a clean, maintainable codebase:

### Repository Pattern

Separates the data access logic from business logic:
- `SongRepositoryInterface` and `SongRepository`
- `UserRepositoryInterface` and `UserRepository`

Benefits:
- Centralized data access logic
- Easier unit testing with mock repositories
- Ability to switch data sources without affecting business logic

### Service Layer Pattern

Contains business logic, working between controllers and repositories:
- `SongService` for song-related operations
- `AuthService` for authentication functions
- `ProfileService` for user profile management

Benefits:
- Clean separation of concerns
- Reusable business logic
- Simplified controllers

### Factory Pattern

Used for testing and database seeding:
- `SongFactory` for creating test song data with various states
- `UserFactory` for creating test user accounts

### Authorization

Role-based access control (RBAC) is implemented:
- Admin-only routes for managing songs
- Middleware for enforcing permission checks
- Policy-based authorization using Laravel's gate system

## 🚀 Why pgvector Instead of ML Libraries?

We chose PostgreSQL's pgvector extension over traditional machine learning libraries for recommendations for several reasons:

1. **Simplicity**: pgvector integrates directly with our database, eliminating the need for separate ML infrastructure
2. **Performance**: Provides efficient nearest-neighbor search for high-dimensional vectors
3. **Real-time Updates**: New songs can be immediately searchable without retraining a model
4. **Cost-effective**: Reduces computational resources compared to running ML models in production
5. **Scalability**: Uses efficient indexing (HNSW) to scale to millions of songs

## 🔍 Vector Similarity Implementation

Our recommendation engine uses the following approach:

1. Song data (title, artist, genre, lyrics) is converted to vector embeddings using OpenAI's embedding API
2. These embeddings capture semantic relationships between songs
3. PostgreSQL's pgvector extension performs fast vector similarity searches
4. For multiple song inputs, we average their embeddings to find similar songs

```sql
-- Example of how similarity search works
SELECT *, 1 - (embedding <=> '[vector]') as similarity
FROM songs
ORDER BY embedding <=> '[vector]'
LIMIT 5;
```

## 🔮 Future Development Plans

1. **Custom Embedding Models**: Train domain-specific embedding models for better music understanding
2. **Hybrid Recommendation System**: Combine content-based and collaborative filtering approaches
3. **User Preference Learning**: Implement models that learn from user interactions over time
4. **Advanced Filtering**: Add mood, tempo, and lyrical content filtering options
5. **Playlist Generation**: Create coherent playlists based on theme, mood, or occasion
6. **Real-time Recommendations**: Process user feedback immediately to improve recommendations

## 🚀 Areas for Improvement

1. **ML-Based Recommendation Model**:
    - Implement a dedicated machine learning model for recommendations
    - Train on user interaction data for personalized recommendations
    - Use techniques like matrix factorization or deep learning for better results

2. **Caching Mechanism**:
    - Add Redis caching for frequently accessed recommendations
    - Cache vector computations to reduce API calls to OpenAI

3. **Improved Frontend Experience**:
    - Add more interactive elements for rating songs
    - Add visualization for music recommendations

4. **Enhanced Analytics**:
    - Track user interaction patterns
    - Provide insights on listening habits
    - Visualize recommendation effectiveness over time

## ⚙️ Setup & Installation

### Prerequisites

- PHP 8.2+
- Composer
- Docker and Docker Compose
- Node.js and npm

### Installation Steps

1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/vibematch.git
   cd vibematch
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install JavaScript dependencies
   ```bash
   npm install
   ```
4. make sure that database configuration in .env similar to docker-compose configuration

5. Start the PostgreSQL container with pgvector extension:
   ```bash
   docker-compose up -d
   ```

6. Run migrations and seed the database
   ```bash
   php artisan migrate --seed
   php artisan db:seed --class=UserSeeder
   php artisan db:seed --class=SongSeeder
   ```

7. Build frontend assets
   ```bash
   npm run build
   ```

8. Start the development server
   ```bash
   composer run dev
   ```

## 👥 User Roles

After seeding, two default users are created:

1. **Admin User**
    - Email: admin@example.com
    - Password: password
    - Can manage songs (add, update, delete)

2. **Regular User**
    - Email: user@example.com
    - Password: password
    - Can browse and get recommendations

## 📚 Performance Optimizations

- HNSW indexing on vector columns for efficient similarity search
- Eager loading relationships to prevent N+1 query issues
- Proper database indexing for frequently queried columns

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.
