ghorg
=====

CLI application to get information about organization on GitHub.

* [Install](#install)
* [Configuration](#config)
* [Usage](#usage)

<a name="install"></a>
## Install

```
git clone git@github.com:gedex/ghorg.git
cd ghorg
composer install
```

You can run `./ghorg` from current directory.

If you want to build the phar file:

```
box build
```

and you can move the file to your OS `PATH`:

```
mv ghorg.phar /usr/local/bin/ghorg
```

Now you can run `ghorg` from anywhere.

<a name="config"></a>
## Configuration

The first time you need to do is configure your `ghorg`, especially `method_auth`
if you want to use GitHub token, client_id/client_secret, or username/password.
If `method_auth` and related auth config keys are left empty then client will
make unathenticated requests. See [GitHub API](https://developer.github.com/v3/) for more detail.

The easiest way to authenticate is by using personal token which can be created
from https://github.com/settings/tokens. You can then set the token with:

```
ghorg config token YOUR_PERSONAL_TOKEN
ghorg config method_auth token
```

Verify whether your config is saved with:

```
ghorg config
```

<a name="usage"></a>
## Usage

### members:list <org>

To list members who are members of an organization:

```
ghorg members:list FriendsOfPHP
```

this will output:

```
+---------+------------+------+------------+
| id      | login      | type | site_admin |
+---------+------------+------+------------+
| 47313   | fabpot     | User | false      |
| 946104  | Hywan      | User | false      |
| 327237  | jubianchi  | User | false      |
| 2716794 | keradus    | User | false      |
| 408368  | lyrixx     | User | false      |
| 282408  | pierrejoye | User | false      |
| 540268  | tarekdj    | User | false      |
+---------+------------+------+------------+
```

You can pass option `-f <fields>` or `--fields=<fields>` to display custom fields.
For example:

```
ghorg members:list -f 'login,html_url'
```

See https://developer.github.com/v3/orgs/members/#response for list of available
fields. If you're wondering how to get member's `followers` or `public_repos`,
like in https://developer.github.com/v3/users/#get-a-single-user, then you need
to pass option `-d` or `--detail` in which it will request member info. It will
take time for organization with thousands of members.

Here's an example that show top 5 members based on number of followers from golang:

```
ghorg members:list golang --detail --fields='login,html_url,followers' --orderby=followers --limit=5

+----------+-----------------------------+-----------+
| login    | html_url                    | followers |
+----------+-----------------------------+-----------+
| bradfitz | https://github.com/bradfitz | 2839      |
| rakyll   | https://github.com/rakyll   | 1697      |
| campoy   | https://github.com/campoy   | 457       |
| dsymonds | https://github.com/dsymonds | 286       |
| dvyukov  | https://github.com/dvyukov  | 269       |
+----------+-----------------------------+-----------+
```

You can also filter returned rows with option `-F <query_string>` or `--filter=<query_string>`.
For example to list members of an `<org>` within San Francisco and hireable:

```
ghorg members:list <org> --detail --fields='login,name,location' -F 'location=San Francisco&hireable=true'
```

Filter uses query string format and there are some comparison operators you can
pass.

```
'==', '===', '!=', '!==', '>', '<', '>=', '<=', '~'
```

Tilde, `~`, is like MySQL's `LIKE` statement. For example to filter `login` that
like `john`:

```
ghorg members:list <org> -F 'login[operator]=~&login[value]=john'
```

### repos:list

Command `repos:list` have similar options like `members:list`, except `--detail`
is not applied. Here's a simple example to list repositories of an organization:

```
ghorg repos:list <org>
```

## License

`ghorg` is licensed under the MIT License - see the LICENSE file for details.
