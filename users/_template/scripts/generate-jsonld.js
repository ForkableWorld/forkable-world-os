#!/usr/bin/env node
const fs = require('fs').promises;
const matter = require('gray-matter');
const path = require('path');

async function generateJsonLd() {
  try {
    // Read profile.md
    const profileContent = await fs.readFile('profile.md', 'utf8');
    const { data: frontmatter, content } = matter(profileContent);

    // Extract sections
    const sections = content.split('\n## ').slice(1).map(section => {
      const [title, ...content] = section.split('\n');
      return { title, content: content.join('\n').trim() };
    });

    // Convert to JSON-LD
    const jsonLd = {
      "@context": "https://schema.org",
      "@type": "Person",
      ...frontmatter,
      description: sections.find(s => s.title === 'Bio')?.content,
      knowsAbout: sections
        .find(s => s.title === 'Interests')
        ?.content
        .split('\n')
        .map(i => i.replace(/^- \*\*(.*?)\*\*:.*$/, '$1'))
        .filter(Boolean),
      seeks: sections
        .find(s => s.title === 'Goals')
        ?.content
        .split('\n')
        .map(g => g.replace(/^\d+\.\s+(.*)$/, '$1'))
        .filter(Boolean)
    };

    // Create public directory if it doesn't exist
    await fs.mkdir('public', { recursive: true });

    // Write JSON-LD
    await fs.writeFile(
      'public/profile.jsonld',
      JSON.stringify(jsonLd, null, 2)
    );

    console.log('Generated JSON-LD profile');
  } catch (error) {
    console.error('Error generating JSON-LD:', error);
    process.exit(1);
  }
}

if (require.main === module) {
  generateJsonLd(); 