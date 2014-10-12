## GitHub Link

Displays GitHub link on the **Plugins** page given there is a `GitHub Plugin URI`
[plugin header](https://github.com/szepeviktor/wordpress-plugin-construction/blob/master/github-link/github-link.php#L10).

When your plugin is on WordPress.org also and there is no `GitHub Branch` header (or its value is "master")
the GitHub icon is displayed **after** other plugin actions (links), otherwise it is the **first** action.

### GitHub headers

- `GitHub Plugin URI` shown as a normal GitHub icon ![GitHub icon](https://raw.githubusercontent.com/szepeviktor/wordpress-plugin-construction/master/github-link/icon/GitHub-Mark-32px.png)
- `GitHub Branch` shown as text after the GitHub icon

| inverted icon |
| -------------:|
|               |
| - `GitHub Access Token` (aka. private repo) shown as an inverted GitHub icon ![GitHub inverted](https://raw.githubusercontent.com/szepeviktor/wordpress-plugin-construction/master/github-link/icon/GitHub-Mark-Light-32px.png) |

### Bitbucket headers

- `Bitbucket Plugin URI` shown as a Bitbucket logo ![Bitbucket logo](https://raw.githubusercontent.com/szepeviktor/wordpress-plugin-construction/master/github-link/icon/bitbucket_32_darkblue_atlassian.png)
- `Bitbucket Branch` shown as text after the Bitbucket icon

### Related Information

These plugin header enable automatic updates to your GitHub or Bitbucket hosted WordPress
plugins and themes using the [GitHub Updater plugin] (https://github.com/afragen/github-updater).
GitHub Updater is not found on WordPress.org.
