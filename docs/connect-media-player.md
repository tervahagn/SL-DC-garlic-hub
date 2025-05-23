# Connect Media Player to the Garlic-Hub

This tutorial describes how a player can securely connect to the server to efficiently detect updates and only download data when necessary. It covers both the HTTP caching mechanisms for optimal performance and the authorization process using User-Agent to ensure secure access to server resources.

**Prerequisites**
You should be familiar with HTTP requests and responses. Especially about Etag, HEAD / GET and what means codes like 301, 304, 200.

## HTTP-Requests

### Modern Method: HTTP Caching with ETag

The recommended modern approach for the pull method uses ETags. Here's how it works:

1. **First Request**: The player sends a request to the server.
2. **Server Response**: The server responds with the content, an ETag (a hash of the content), and a Last-Modified timestamp.
3. **Subsequent Requests**: For later requests, the player sends the previously received ETag in the `If-None-Match` header.
4. **Server Decision**:
    - If the content hasn't changed, the server responds with `304 Not Modified`
    - If the content has been updated, the server sends `200 OK` with the new content and a new ETag

### Older Method: If-Modified-Since

The older, less precise method uses the `If-Modified-Since` header:

1. The player sends the timestamp of the last known update in the `If-Modified-Since` header.
2. The server compares this with the modification time of the requested resource.

This method is less accurate than ETags and is only used when no ETag is available.

### Priority of Methods

The server prioritizes requests as follows:
1. If `If-None-Match` is sent and matches → `304 Not Modified`
2. If no `If-None-Match` or no match → Check `If-Modified-Since`
3. If `If-Modified-Since` is present and the date is older than the last modification → `200 OK` with content
4. If `If-Modified-Since` is current → `304 Not Modified`

### Implementation for Players

See the player as a client like a webbrowser. We use the same mechanisms.

#### Example Flow:

1.1. **Player initial HEAD request when empty**:
```
HEAD /index.smil HTTP/1.1
   Host: example.com
```
1.2. **Player initial HEAD request when file is already cached**:
```
HEAD /index.smil HTTP/1.1
   Host: example.com
   If-None-Match: "a1b2c3d4e5f6"
   If-Modified-Since: Wed, 15 Nov 2023 12:30:45 GMT
```

Sending If-Modified-Since is optional in case you send If-None-Match

2.1. **Server response if unchanged**:
```
HTTP/1.1 304 Not Modified
   ETag: "a1b2c3d4e5f6"
   Last-Modified: Wed, 15 Nov 2023 12:30:45 GMT
   Cache-Control: public, must-revalidate, max-age=864000, pre-check=864000
```

2.2. **Server response if changed**:
```
HTTP/1.1 200 OK
   Content-Type: application/smil+xml
   ETag: "m1nopqr2s4t6u7v8w9x0yz"
   Last-Modified: Wed, 15 Nov 2023 12:30:45 GMT
   Cache-Control: public, must-revalidate, max-age=864000, pre-check=864000
```

3.1. **Player second Request (GET) when server answered with 200**:
```
GET /index.smil HTTP/1.1
   Host: example.com
   If-None-Match: "a1b2c3d4e5f6"
   If-Modified-Since: Wed, 15 Nov 2023 12:30:45 GMT
```

4.1. **Server response to GET if unchanged**:
```
HTTP/1.1 304 Not Modified
   ETag: "a1b2c3d4e5f6"
   Last-Modified: Wed, 15 Nov 2023 12:30:45 GMT
   Cache-Control: public, must-revalidate, max-age=864000, pre-check=864000
```
> **Note**: Some players might skip the initial HEAD request and directly send a GET. In such cases, the server will still check for changes and respond with 304 Not-Modified if nothing has been updated.

4.2. **Server Response if changed**:
```
HTTP/1.1 200 OK
   Content-Type: application/smil+xml
   ETag: "m1nopqr2s4t6u7v8w9x0yz"
   Last-Modified: Wed, 15 Nov 2023 12:30:45 GMT
   Cache-Control: public, must-revalidate, max-age=864000, pre-check=864000
   
   [CONTENT]
```

### Summary

- Always use ETags via `If-None-Match` when available
- Send `If-Modified-Since` as a fallback
- The server returns `Cache-Control`, `ETag`, and `Last-Modified` headers
- The ETag for index.smil is an MD5 hash of the content
- The etag for media will be determined from the webserver by default
- This method minimizes data traffic and allows for efficient updates

## Authentication with User-Agent

Garlic-Hub will detect the player according to his User-Agent.
To be compatible with SMIL player from IAdea and QBic International player should use:

following format:

```
 API-ID (UUID:Universally-Unique-Identifier; NAME:Playername) Firmware-version (MODEL:Model-ID)
```

### Explanation:
- **API-ID**: Name of the Rest API your player use.  
- **UUID**: Universally Unique Identifier.THis should be definitely unique!
- **NAME**: Name of the player which will be shown in the overview.
- **Firmware-version**: A string of your current app or firmware version
- **MODEL**: A short ID without spaces to detect specific player and give them probably a different SMIL index

#### API Explanation

IAdea and Garlic player have an API reachable via http://localhost:8080. Look at [Garlic-Rest-API](https://garlic-player.com/garlic-player/docs/rest-api/) for details. You can use it to push indexes, media, configurations and more.

In our case it is irrelevant, but it is required to have this ID first. If you are in doubt what to use and want t be compatible simply use GAPI/1.0.  

#### Examples

The Garlic player sends this user agent 

```
 GAPI/1.0 (UUID:a8294bat-c28f-50af-f94o-800869af5854; NAME:Player with spaces in name) garlic-linux/v0.6.0.745 (MODEL:Garlic)
```

The XMP-330 from IAdea sends following user-agent:

```
 ADAPI/1.0 (UUID:b8294bat-c28f-50af-f94o-800869af5854; NAME:Player with spaces in name) SK8855-ADAPI/2.0.5 (MODEL:XMP-330)
```

The first 4K player from IAdea use: 

```
ADAPI/2.0 (UUID:22a6d755-8ca6-4a82-a724-2cc548000d06) RK3288-ADAPI/1.0.3.74 (MODEL:XMP-7300)
```

Please leave a request in the issues if you need support for your player. 

## Passwords

There is also an option to use a user authentication with a password in SMIL header, but currently most player do not use. 