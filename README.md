![logo](https://hackerdig.com/img/logo-50x50.png)

Hacker Dig is a custom Hacker News interface, enhanced with summaries and special functionalities that help the user to dig Hacker News for ideas and success opportunities.

See it live on __https://hackerdig.com__

The app is written in Vue.js and PHP. The source code of the app is provided in this repository.
<br>
<br>

__Quickstart__

To run Hacker Dig in your PHP & MySQL environment:

1. Edit `/setup/config.php` file with your login details.
2. Open `/setup/create_db.php` in your browser to create MySQL database tables.
3. Open `/backend/cron.php` in your browser to fetch new content from APIs.
4. Then to start the app, open `index.html` file in your browser.
<br>
<br>

__How Hacker Dig works__

First of all, this tool puts the user into the mindset required for idea digging. If browsing Hacker News directly, the mind quickly shifts to a 'curiosity' or an 'entertainment' mode, and forgets (or doesn't have this purpose in the first place) to interpret everything creatively and look for potentially great ideas and opportunities. 

This mindset change on Hacker Dig is done by setting the correct expectations at the beginning (here we dig for ideas) and then creating a particularly limited interface which displays only as much information as necessary for digging, and allows to scan through titles, summaries and comments quickly, and in a plain text mode, without any visual or stylistic distractions.

Inspired by the different topics and concepts from Hacker News, the user can then transform this inspiration into innovative ideas and with a one-click note them in the "Ideas" view of the app. Each new note has a reference to the original HN story or a comment.

All ideas are saved as cookies. To archive ideas, you can send them to your email address and then clear the "Ideas" view and start over.

Updates from Hacker News API come every 10 minutes. For summaries extraction, the app uses Aylien API.

Hacker Dig is a PWA app. Based on the CSS grid layout, it can be comfortably used on both narrow and wide screens. With Google Chrome you can install the app on your mobile or Windows desktop device and use it as a native app.
<br>
<br>

__Issues__

Report issues in [issue tracker](https://github.com/giekaton/hacker-dig/issues).
<br>
<br>

__Contribution__

Feel free to make a pull request or suggest ideas.
<br>
<br>

__Screenshots__

![shot-01](https://hackerdig.com/img/shot-01.png)

![shot-02](https://hackerdig.com/img/shot-02.png)

![shot-03](https://hackerdig.com/img/shot-03.png)
