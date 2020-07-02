

# MAINPAGE

<p align="center">
	<img src="https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2Fjcobs-engine%2FMAINPAGE%2Fmaster%2Fmetadata.json&label=version&query=version&color=success&style=flat">
	<img src="https://img.shields.io/badge/build-passing-success.svg?style=flat">
	<img src="https://img.shields.io/badge/license-GNU%20General%20Public%20License%20v3.0-blue.svg?style=flat">
	<img src="https://img.shields.io/badge/requires-WebServer-black.svg?style=flat">
	</p>
<p align="center">
	<img src="https://img.shields.io/github/watchers/jcobs-engine/MAINPAGE?style=social">
	<img src="https://img.shields.io/github/stars/jcobs-engine/MAINPAGE?style=social">
    <img src="https://img.shields.io/github/forks/jcobs-engine/MAINPAGE?style=social">
</p>

**MAINPAGE** is a *free* and *open source* social network platform. It is easy to install and use on every Linux System.

## Installation

### OpenSuse

```Shell
user@localhost:~> git clone https://github.com/jcobs-engine/MAINPAGE.git
user@localhost:~> cd MAINPAGE/
user@localhost:~/MAINPAGE> bash INSTALL.sh
```
### Other Distributions

1. Install **Apache2**, **MySQL** and **PHP7**.
2. Download Symbols:
   ```shell
   bash GET_EMOJIS.sh
   ```
3. Copy the MAINPAGE-Data into Apache2-Directory. 
4. Set up database: 
   ```Shell
   user@localhost:~> mysql -u root --password='' < install.sql
   user@localhost:~> mysql -u MAINPAGE --password='MAINPAGE' MAINPAGE <   MAINPAGE.sql
   ```
### Troubleshooting

- Try disable or configure Firewall
- Check permissions of WebServer-Directory
- **Ask in Issues!**

## Usage

After successful installation, you can open MAINPAGE with the IP-address in a browser.

## Credits

- **Emanuil Rusev**, Great Markdown Parser for PHP: https://github.com/erusev/parsedown