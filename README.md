![FLOW hero section](images/flow-hero.png)

**FLOW** is an intuitive web application designed for seamless document sharing
and collaborative text file editing. Built with **PHP, CSS, JavaScript, and
SQLite**, it offers a straightforward and efficient user experience.

**Disclaimer:** This web application was developed as a final project for a
university Web Development course. **As such, it has not been tested for
security or thoroughly hardened for production use.**

## Features

- **User Accounts:** Register and authenticate securely.
- **Project Management:** Create and organize your projects.
- **Document Lifecycle:** Create, upload, and delete project-specific documents.
- **Team Collaboration:** Invite members to projects for shared document access.

## Deployment

To deploy FLOW, you'll need Docker installed.

1. **Edit Nginx Configuration:** Adjust the `nginx/default.conf` file to suit
   your environment. The default configuration hosts HTTP on `localhost`.
2. **Start Docker Compose:** Run the following command in your project's root
   directory:

   ```bash
   docker compose up -d --build
   ```

_Note:_ For HTTPS, and/or TLS/SSL, you will need to manually configure Nginx
beyond the provided default.

## License

This project is licensed under the [GNU General Public License v3.0 (GPLv3)](https://www.gnu.org/licenses/gpl-3.0.en.html).

