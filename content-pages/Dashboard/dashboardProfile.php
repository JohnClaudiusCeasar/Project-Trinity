<!-- Profile Content Fragment -->
<main class="profile-page">
  <div class="profile-container">
    
    <!-- Cover Photo Wrapper -->
    <div class="cover-section">
      <!-- Main Cover Photo -->
      <div class="cover-photo">
        <button class="btn-change-cover">
          <mat-icon class="icon font-light">image</mat-icon>
          <div class="divider-vertical"></div>
          <span>Change Cover Photo</span>
        </button>
      </div>
      
      <!-- Thin Bar Below Cover -->
      <div class="cover-bar"></div>

      <!-- Avatar Profile Picture -->
      <div class="profile-cover-avatar"></div>
    </div>

    <!-- Profile Info Content -->
    <div class="profile-details">
      <h1 class="username"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Username'); ?></h1>
      <p class="email"><?php echo htmlspecialchars($_SESSION['email'] ?? 'email@example.com'); ?></p>

      <!-- Stats -->
      <div class="stats-row">
        
        <div class="stat-block">
          <span class="stat-label">Collaborators:</span>
          <span class="stat-value">679</span>
        </div>
        
        <div class="stat-divider"></div>
        
        <div class="stat-block">
          <span class="stat-label">Contributions:</span>
          <span class="stat-value">431</span>
        </div>
        
        <div class="stat-divider"></div>
        
        <div class="stat-block">
          <span class="stat-label">Entries:</span>
          <span class="stat-value">75</span>
        </div>
        
      </div>

      <!-- ABOUT ME Section -->
      <div class="about-section">
        <h2 class="about-title">ABOUT ME</h2>
        
        <div class="about-box">
          <span class="about-placeholder">Insert Caption...</span>
        </div>
        
        <div class="edit-action">
          <button class="btn-edit">
            Edit
          </button>
        </div>
      </div>
      
    </div>
  </div>
</main>