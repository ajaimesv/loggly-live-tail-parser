# loggly live tail parser

A simple parser for loggly's live tail results.

This project does not have any dependency; just put it on your webserver and enjoy.

You can paste multiple lines in the textarea. Loggly's live tail results look like:

```
X-Forwarded-For:192.168.0.1, X-Real-IP:192.168.0.1 ] {"timestamp":"2019-07-22T15:21:35.095Z","service":"service-name","message":"The service is alive","HOSTNAME":"demo","level":"INFO","thread_name":"main-thread","environment":"qa","port":8080,"logger_name":"access.success","appname":"my-service-demo","appid":"my-service-demo","hostname":"localhost","region":"us"}
```

Do you want to see it working? copy the previous sample log and paste it here: [clean logs](https://andres.jaimes.net/clean-logs.php).
