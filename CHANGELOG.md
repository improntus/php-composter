# ChangeLog

### 0.1.0 (24/03/2016)
#### Added
- Initial release to GitHub.

### 0.1.1 (24/03/2016)
#### Added
- Pass `$hook` and `$root` variables to each action.

### 0.1.2 (25/03/2016)
#### Added
- Graceful error-handling in bootstrapping process.
- Several comments added.
- Notice about package name requirements added to `README.md`.

### 0.1.3 (25/03/2016)
#### Added
- Refactor bootstrapping to use an instantiated object.
- i18n all strings.

#### Fixed
- Updated `README.md` with refactoring changes.

### 0.2.0 (28/03/2016)
#### Added
- List of existing actions in `README.md`.

#### Changed
- Hooks in `extra` key are regrouped under a new `php-composter-hooks` key.
- Don't require package name vendor to be `php-composter`.

### Fixes
- Correct `README.md` badges.
- Alternative way of simulating JSON comments in `README.md`.

### 0.3.0 (14/07/2018)
#### Added
- Add unit tests.
- Add ability to return a temporary checkout of staged content changes.
- Add `error` output method to BaseAction.
- Add `success` message output method to BaseAction.
- Add `title` display method to BaseAction.
- Add a method to skip an action.
- Add formatting to IO methods.
- Add path to Composer file.
- Introduce `getExtraKey()` method to BaseAction.

#### Changed
- Ensure that paths to symlink into actually exist.
- Remove translatable strings, they are not a good fit for a CLI tool.
- Pass additional arguments from shell stub to the bootstrap script.
- Pass arguments from bootstrap file to action.
- Implemented relative symlink with absolute symlink fallback. Also added understandable errors, especially for Windows where privileges could be an issue.
- Added quotes around path of bootstrap.php to make sure paths with spaces are interpreted correctly.
- Remove hard requirement for package name prefix
- Instead of all modified file, get git staged files to current commit.

### 0.3.1 (14/07/2018)
#### Changed
- Only show file exists warning in very verbose mode.

### 0.3.2 (22/07/2018)
#### Changed
- Adapt code to make it compatible with PHP 5.4.

### 0.3.3 (04/08/2018)
#### Changed
- Make bootstrap file more robust.

### 1.0.1 (19/07/2023)
#### Changed
- Make package compatible with php 7.4 and 8.1

### 1.0.2 (19/07/2023)
#### Changed
- Change vendor name to Improntus

### 1.1.0 (19/07/2023)
#### Changed
- Re-factorize to Improntus property
- Fix bugs from newer version of composer
- Remove /dev/tty and add chmod for symlinks

### 1.1.1 (19/07/2023)
#### Changed
- Remove /dev/tty and add chmod for symlinks

### 1.1.2 (19/07/2023)
#### Changed
- Fix bugs from refactor

### 1.1.3 (19/07/2023)
#### Changed
- Add chmod to post install cmd

### 1.1.4 (21/07/2023)
#### Changed
- Check if folder is writeable before making changes

### 1.1.5 (31/07/2023)
#### Changed
- Fix some "is_writeable" functions pointing to the wrong folder

### 1.1.6 (31/07/2023)
#### Changed
- Remove emptying folder .git/php-composter/includes
- Add remove composter bootstrap symlink to create it again

### 1.1.7 (31/07/2023)
#### Changed
- Ensure that .git/php-composter folder exists

### 1.1.8 (31/07/2023)
#### Changed
- Avoid removing folder unnecesary
