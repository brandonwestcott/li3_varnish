# This is a basic VCL configuration file for varnish using li3_varnish library.  See the vcl(7)
# man page for details on VCL syntax and semantics.
# 
# Default backend definition.  Set this to point to your content
# server.
# 
backend default {
	.host = "127.0.0.1";
	.port = "80";
}

acl purge_acl {
	"localhost";
}

sub vcl_recv {

	# purge request if client IP is valid
	if (req.request == "PURGE") {
		if (!client.ip ~ purge_acl) {
			error 405 "Not allowed.";
		}
		return (lookup);
	}

	# not a valid verb, pipe directly to backend
	if (req.request != "GET" &&
		req.request != "HEAD" &&
		req.request != "PUT" &&
		req.request != "POST" &&
		req.request != "TRACE" &&
		req.request != "OPTIONS" &&
		req.request != "DELETE") {
			return (pipe);
	}

	if (req.http.Expect) {
		return (pipe);
	}

	# only GET && HEAD
	if (req.request != "GET" && req.request != "HEAD") {
		return (pass);
	}

	# Remove cookies
	# if(req.http.Cookie) {
	#	remove req.http.Cookie;
	# }

	if (req.http.Authorization) {
		return (pass);
	}

	return (lookup);
}

sub vcl_pipe {
    # Note that only the first request to the backend will have
    # X-Forwarded-For set.  If you use X-Forwarded-For and want to
    # have it set for all requests, make sure to have:
    # set bereq.http.connection = "close";
    # here.  It is not set by default as it might break some broken web
    # applications, like IIS with NTLM authentication.
    return (pipe);
}

sub vcl_pass {
    return (pass);
}

sub vcl_hash {
	hash_data(req.url);
	if (req.http.host) {
		hash_data(req.http.host);
	} else {
		hash_data(server.ip);
	}
	return (hash);
}

sub vcl_hit {
	if (req.request == "PURGE") {
		purge;
		error 200 "Purged.";
	}
	return (deliver);
}

sub vcl_miss {
	if (req.request == "PURGE") {
		purge;
		error 200 "Purged.";
	}
	return (fetch);
}

sub vcl_fetch {
	# if ESI header, set varnish to do ESI processing
	if(beresp.http.esi-enabled == "1"){
		set beresp.do_esi = true; 
		unset beresp.http.esi-enabled;
	}

	# passthrough set to false, delete expires and cache control
	if(beresp.http.passthrough-cache != "1"){
		if(beresp.http.expires){
			unset beresp.http.expires;
		}
		if(beresp.http.cache-control){
			unset beresp.http.cache-control;
		}
	}

	if (beresp.ttl > 0s && beresp.http.Set-Cookie) {
		unset beresp.http.Set-Cookie;
	}

	return (deliver);
}

sub vcl_deliver {
	if (obj.hits > 0) {
		set resp.http.X-Cache = "HIT";
		set resp.http.X-Cache-Hits = obj.hits;
	} else {
		set resp.http.X-Cache = "MISS";
	}
	return (deliver);
}

sub vcl_error {
	# unique ident url via varnish
	set obj.http.Content-Type = "text/html; charset=utf-8";
	set obj.http.Retry-After = "5";
	synthetic {"
	<?xml version="1.0" encoding="utf-8"?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html>
			<head>
				<title>"} + obj.status + " " + obj.response + {"</title>
			</head>
			<body>
				<h1>Error "} + obj.status + " " + obj.response + {"</h1>
				<p>"} + obj.response + {"</p>
				<h3>Guru Meditation:</h3>
				<p>XID: "} + req.xid + {"</p>
				<hr>
				<p>Varnish cache server</p>
			</body>
		</html>
	"};
	return (deliver);
}

sub vcl_init {
	return (ok);
}

sub vcl_fini {
	return (ok);
}
