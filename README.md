# Jut.su anime downloader
**jutsu-dl is a simple tool for downloading anime from jut.su, which can also be used as a backend for your application**\
Compiled packages can be downloaded [here](https://github.com/ZzEdovec/jutsu-dl/releases)
## HOW TO USE
**!! You must have the latest version of [JRE 8](https://www.java.com) to run jutsu-dl** *(compatibility with openjre has not been tested, but if you use it, you need openjfx)*

The approximate input of the download command looks like this

    java -jar jutsu-dl.jar [link to film/episode]
For example

    java -jar jutsu-dl.jar https://jut.su/yakusoku-neverland/season-1/episode-1.html

jutsu-dl also support arguments

`-q [360/480/720/1080/lowest/highest/default]` for quality select\
`-d [DEST DIR]` for destination directory select\

For example

    java -jar jutsu-dl.jar https://jut.su/yakusoku-neverland/season-1/episode-1.html -d ~/Videos -q 720
## HOW TO BUILD
You will need [DevelNext](https://develnext.org)

1. Clone the repository to local drive
2. Open jutsu-dl.dnproject file in DevelNext
3. Click the build button in the top menu
## DONATE
**For those who want to send a voluntary donation**

If you live in Russia or can send money in rubles, please use [this link](https://yoomoney.ru/to/4100116276215735)\
For other countries and currencies, use [this](https://www.donationalerts.com/r/queinu)
## Friendly reminder
jutsu-dl will not help to bypass the regional blocking of some anime seasons.
