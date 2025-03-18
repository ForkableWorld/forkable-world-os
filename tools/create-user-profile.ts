#!/usr/bin/env node
import { execSync } from 'child_process';
import { promises as fs } from 'fs';
import path from 'path';

async function createUserProfile(username: string): Promise<void> {
  try {
    console.log(`Creating profile repository for ${username}...`);
    
    // 1. Verify GitHub CLI is installed
    try {
      execSync('gh --version');
    } catch (error) {
      console.error('GitHub CLI is not installed. Please install it first:');
      console.error('winget install GitHub.cli');
      process.exit(1);
    }

    // 2. Verify GitHub authentication
    try {
      execSync('gh auth status');
    } catch (error) {
      console.error('Please login to GitHub first:');
      console.error('gh auth login');
      process.exit(1);
    }

    // 3. Create new repository from template
    const repoName = `forkable-world-profile`;
    await createGitHubRepo(username, repoName);
    
    // 4. Clone the template locally
    console.log('Cloning template...');
    execSync(`git clone https://github.com/${username}/${repoName}.git temp-${username}`);
    
    // 5. Copy template files
    const templateDir = path.join(__dirname, '../users/_template');
    await fs.cp(templateDir, `temp-${username}`, { recursive: true });
    
    // 6. Customize template for user
    await customizeTemplate(username, `temp-${username}`);
    
    // 7. Push template to new repo
    console.log('Pushing template to your repository...');
    execSync(`
      cd temp-${username} &&
      git add . &&
      git commit -m "Initial profile setup" &&
      git push origin main
    `);
    
    // 8. Add and initialize submodule
    console.log('Adding submodule to main repository...');
    execSync(`
      git submodule add -b main https://github.com/${username}/${repoName}.git users/${username} &&
      cd users/${username} &&
      git checkout main &&
      cd ../.. &&
      git add .gitmodules users/${username} &&
      git commit -m "Add ${username}'s profile as submodule"
    `);
    
    // 9. Cleanup
    await fs.rm(`temp-${username}`, { recursive: true, force: true });
    
    console.log(`
Profile setup complete! 

Your profile is now available at:
- Repository: https://github.com/${username}/${repoName}
- Submodule: users/${username}

Next steps:
1. Edit your profile at users/${username}/profile.md
2. Run validation: npm run validate users/${username}/profile.md
3. Commit and push your changes
4. Your profile will be automatically validated via GitHub Actions

To update your profile in the main repository:
1. Push changes to your profile repository
2. Update the submodule:
   cd forkable-world-os
   git submodule update --remote users/${username}
   git add users/${username}
   git commit -m "Update ${username}'s profile"
   git push
    `);
    
  } catch (error) {
    console.error('Error creating profile:', error instanceof Error ? error.message : String(error));
    // Cleanup on error
    try {
      execSync(`rm -rf temp-${username}`);
    } catch {
      // Ignore cleanup errors
    }
    process.exit(1);
  }
}

async function createGitHubRepo(owner: string, name: string): Promise<void> {
  try {
    execSync(`gh repo create ${name} --public --template forkable-world/profile-template`);
    console.log(`Created repository: ${owner}/${name}`);
  } catch (error) {
    console.error('Error creating repository:', error instanceof Error ? error.message : String(error));
    throw error;
  }
}

async function customizeTemplate(username: string, dir: string): Promise<void> {
  // Read the template profile
  const profilePath = path.join(dir, 'profile.md');
  const profile = await fs.readFile(profilePath, 'utf8');
  
  // Update with user info
  const updatedProfile = profile
    .replace('name: Example User', `name: ${username}`)
    .replace('description: An example user profile', `description: ${username}'s profile`);
  
  // Save changes
  await fs.writeFile(profilePath, updatedProfile);
}

// CLI interface
if (require.main === module) {
  const username = process.argv[2];
  if (!username) {
    console.error('Please provide your GitHub username');
    process.exit(1);
  }
  
  createUserProfile(username);
} 