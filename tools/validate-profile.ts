import yaml from 'js-yaml';
import matter from 'gray-matter';
import Ajv from 'ajv';
import addFormats from 'ajv-formats';
import fetch from 'node-fetch';
import { promises as fs } from 'fs';

interface Skill {
  name: string;
  level?: 'Beginner' | 'Intermediate' | 'Advanced' | 'Expert';
}

interface PersonSchema {
  '@context': 'https://schema.org';
  '@type': 'Person';
  name: string;
  description?: string;
  knowsAbout?: string[];
  skills?: Skill[];
}

// Schema.org Person schema
const personSchema = {
  type: 'object',
  properties: {
    '@context': { const: 'https://schema.org' },
    '@type': { const: 'Person' },
    name: { type: 'string' },
    description: { type: 'string' },
    knowsAbout: {
      type: 'array',
      items: { type: 'string' }
    },
    skills: {
      type: 'array',
      items: {
        type: 'object',
        properties: {
          name: { type: 'string' },
          level: { 
            type: 'string',
            enum: ['Beginner', 'Intermediate', 'Advanced', 'Expert']
          }
        },
        required: ['name']
      }
    }
  },
  required: ['@context', '@type', 'name']
} as const;

async function validateProfile(filePath: string): Promise<boolean> {
  try {
    // Parse markdown frontmatter
    const fileContent = await fs.readFile(filePath, 'utf8');
    const { data: frontmatter, content } = matter(fileContent);

    // Initialize validator
    const ajv = new Ajv({ allErrors: true });
    addFormats(ajv);
    const validate = ajv.compile<PersonSchema>(personSchema);

    // Validate frontmatter against Schema.org Person schema
    const valid = validate(frontmatter);

    if (!valid) {
      console.error('Validation errors:');
      console.error(validate.errors);
      return false;
    }

    // Validate markdown content sections
    const sections = content.split('\n## ');
    const requiredSections = ['Bio', 'Interests', 'Goals'];
    const missingSections = requiredSections.filter(section => 
      !sections.some(s => s.startsWith(section))
    );

    if (missingSections.length > 0) {
      console.error('Missing required sections:', missingSections);
      return false;
    }

    // Additional custom validations can go here
    // For example, checking if interests match knowsAbout in frontmatter

    return true;
  } catch (error) {
    console.error('Error validating profile:', error instanceof Error ? error.message : String(error));
    return false;
  }
}

// CLI interface
if (require.main === module) {
  const filePath = process.argv[2];
  if (!filePath) {
    console.error('Please provide a profile file path');
    process.exit(1);
  }

  validateProfile(filePath).then(valid => {
    if (!valid) {
      process.exit(1);
    }
    console.log('Profile is valid!');
  });
}

export { validateProfile, PersonSchema, Skill }; 