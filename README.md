# Doky CMS PHP

A PHP CMS that syncs the documentation written to and from a git repository.

The basic idea of this CMS is that the documentation written to it will be
available both in the development repository as markdown files but then also
be available for the public.

## Tasks/Roadmap

First Basic Version

- Synchronizer
  - Git
    - [x] Cloning repository
    - [x] Pulling changes
    - [ ] Pushing changes
    - [ ] Creating detailed commit messages
    - [ ] Webhook for updates
    - [ ] Uploading to specific branch
    - [ ] Serving from specific directory
- Backend
  - [ ] Compression of images
  - [ ] Index of all docs
- CMS
  - Roles
    - [ ] Role-based permission system
  - Users
    - [ ] Author name in doc
    - [ ] Co-authoring
  - Editor
    - [ ] Markdown editor
    - Meta
      - [x] Basic Key-Value pairs
      - [ ] YAML 
- Frontend
  - Search
    - [ ] Advanced search per word
    - [ ] Priority sorting with doc meta
  - Keybinds
    - [ ] Open search overlay
  - [ ] Mobile support
- API
  - Documentation
    - [ ] Markdown
    - [ ] Meta data
