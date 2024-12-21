# INTSEC: Hardening Passoire Web Application

This is a group project for the INTSEC course at Stockholm University. The idea of the project is to harden a web application full of vulnerabilities such that other groups are not able to capture the flags that reside inside.

## Phases
There were three phases for the project:
1. **Phase 1:** Pull the application from Dockerhub and explore the container. In this phase, we are tasked with finding all the flags that exist, and the vulnerabilities that the application contains.
2. **Phase 2:** Patch the vulnerabilities of the application without affecting the functionalities of the web app. This involved:
    - Linux system level changes
    - Changes to the Apache server configuration
    - Patching the PHP code of the application
    - Patching the NodeJS server
    - Hardening the MySQL database configuration
3. **Phase 3:** Attack the web application of other groups to find as many flag as possible. In this phase, we used knowledge of the vulnerabilities of the original image to attack other groups. It also involved coming up with newer ways to break into the system.

## Getting Started
First, clone the repository:
```bash
git clone https://github.com/Warrior-47/intsec-group-project.git
```
Change the value of `GROUP_SECRET` in `startup.sh` script to any random string value. Then, execute the script:
```bash
./startup.sh
or
bash startup.sh
```
The container will be named passoire. The web application will be available on port 8080, SSH on port 8081 and the NodeJS server on port 3002.
<br>

>The base image was built for amd64. If you are using a Mac with arm64, you have to use colima or other similar tools.
