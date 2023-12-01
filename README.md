# Doky CMS PHP

A PHP CMS that syncs the documentation written to and from a git repository.

The basic idea of this CMS is that the documentation written to it will be
available both in the development repository as markdown files but then also
be in a database. This way the documentation can be written to in a web
interface and then be synced to the repository.

## Tasks/Roadmap

First Basic Version

- Synchronizer
  - Git
    - [x] Cloning repository
    - [x] Pulling changes
    - [x] Pushing changes
    - [ ] Creating detailed commit messages
    - [x] Webhook for updates
    - [x] Uploading to specific branch
    - [x] Serving from specific directory
- Backend
  - [ ] Compression of images
  - [ ] Index of all docs
  - [x] Serialization of docs into markdown
- CMS
  - Roles
    - [x] Role-based permission system
  - Users
    - [x] Author name in doc
    - [x] Co-authoring
  - Editor
    - [x] Markdown editor
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
