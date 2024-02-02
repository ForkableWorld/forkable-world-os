<style type="text/css">
    body {
        padding: 1em;
    }

    h1 {
        text-align: center;
    }

    .item {
        box-shadow: 0px 0px 1em #f0f0f0;
        margin: 1em 0;
        transition: background 2s;
    }

    .item:focus {
        background-color: #FFFFA0;
    }

    .item table {
        padding: 1em 0;
    }

    .item h2 {
        margin-bottom: 0.2em;
    }

    a {
        text-decoration: none;
    }

    .allItems {
        margin: 0 auto;
        width: auto;
    }

    .votes {
        padding: 1em;
        margin: 0 1em 0.2em 1em;
        border: 1px solid #88bbff;
        border-radius: 5px;
        text-align: center;
        background: linear-gradient(#f8f8ff, #fafaff);
    }

    .votebutton {
        padding: 0.2em 1em 0.2em 1em;
        margin: 0 1em 0 1em;
        text-align: center;
        border: 1px solid #7ae;
        border-radius: 5px;
        display: block;
        background: linear-gradient(#a8c8ff, #badaff);
        border: 1px solid #7ae;
    }

    .votebutton:hover {
        background: linear-gradient(#b8d8ff, #aacaff);
    }

    .voteInfo {
        width: 8em;
    }

    .ideaArea {
        max-width: 60em;
    }

    .voteAuthor {
        color: #888;
    }

    .ideaBottom {
        color: #888;
        font-size: 90%;
    }

    .voteAuthor img {
        width: 20px;
        height: 20px;
        border-radius: 5px;
        vertical-align: bottom;
    }

    tr {
        vertical-align: top;
    }

    .container {
        display: inline-block;
    }

    .content_wrapper {
        text-align: center;
    }

    .noalign {
        text-align: left;
    }

    .otherLabels {
        text-align: center;
        padding-top: 1em;
        border: 1px solid #e8e8e8;
        background: #f4f4f4;
    }

    .otherLabels div {
        padding-bottom: 1em;
        color: #666;
    }

    .otherLabel {
        color: #fff;
        box-shadow: inset 0 -1px 0 rgba(27, 31, 35, 0.12);
        display: inline-block;
        padding: 0 10px;
        margin: 5px;
        font-size: 16px;
        font-weight: 600;
        line-height: 2;
        text-align: center;
        border-radius: 3px;
        -webkit-transition: opacity 0.2s linear;
        transition: opacity 0.2s linear;
        border: 1px solid #eee;
    }

    .otherLabel .name {

    }

    #getissues-error {
        padding: 1em;
        color: red;
    }

    .issueLabels {
        padding-left: 2em;
    }

    .tip {
        text-align: center;
        padding-top: 2em;
    }

    .btn {
        margin-left: 0.5em;
        background: #3498db;
        background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
        background-image: -moz-linear-gradient(top, #3498db, #2980b9);
        background-image: -ms-linear-gradient(top, #3498db, #2980b9);
        background-image: -o-linear-gradient(top, #3498db, #2980b9);
        background-image: linear-gradient(to bottom, #3498db, #2980b9);
        -webkit-border-radius: 28;
        -moz-border-radius: 28;
        border-radius: 28px;
        font-family: Arial;
        color: #ffffff;
        font-size: 15px;
        padding: 4px 20px 4px 20px;
        text-decoration: none;
        display: inline-block;
        background: #3498db;
    }

    .btn:hover {
        background: #3cb0fd;
        background-image: -webkit-linear-gradient(top, #3cb0fd, #3498db);
        background-image: -moz-linear-gradient(top, #3cb0fd, #3498db);
        background-image: -ms-linear-gradient(top, #3cb0fd, #3498db);
        background-image: -o-linear-gradient(top, #3cb0fd, #3498db);
        background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
        text-decoration: none;
    }
</style>

<script>
    var githubIssueVote = new function() {
        var that = this;
        var templateID;
        var gitHubPath;
        var token;

        this.init = function(gitHubPath, templateID, labels, token) {
            this.templateID = templateID;
            this.gitHubPath = gitHubPath;
            this.labels = labels;
            if (token != "") {
                this.token = token;
            }
        }

        this.buildIssues = function() {
            var loadingObj = document.getElementById("getissues-loading");
            if (typeof loadingObj != "undefined" && loadingObj != null) {
                loadingObj.style['display'] = "";
            }

            var request = new XMLHttpRequest();
            request.onreadystatechange = function() {
                that.issues_state_change(request)
            };
            var url = "https://api.github.com/repos/" + this.gitHubPath + "/issues?state=open";
            if(this.labels){
                url += "&labels=" + encodeURIComponent(this.labels);
            }
            url +=  "&per_page=100&cb=" + this.s4();
            request.open("GET", url, true);
            request.setRequestHeader("X-Requested-With", 'IssueVote (OAuth App; org/BiglySoftware; user/TuxPaper; XMLHttpRequest)');
            request.setRequestHeader("Accept",
                "application/vnd.github.squirrel-girl-preview,application/vnd.github.v3.html+json"
            );
            if (typeof this.token !== "undefined") {
                request.setRequestHeader("Authorization", "token " + this.token);
            }
            request.send(null);
        };

        this.issues_state_change = function(request) {
            if (request.readyState == 4) { // 4 = "loaded"
                var loadingObj = document.getElementById("getissues-loading");
                if (typeof loadingObj != "undefined" && loadingObj != null) {
                    loadingObj.style['display'] = "none";
                }
                if (request.status == 200) {
                    // console.log(request.responseText);
                    var jsonResponse = JSON.parse(request.responseText);
                    var hasMore = request.getResponseHeader("Link") !== null;
                    this.showVoteItems(jsonResponse, hasMore);
                    // Normally we don't need to do this, as an achor will focus a div
                    // with a tabIndex.  However, we are adding divs dynamically, so we
                    // need to explicitly focus
                    var anchor = getAnchor();
                    if (anchor != null) {
                        var focusObj = document.getElementById(anchor);
                        if (typeof focusObj != "undefined" && focusObj != null) {
                            focusObj.focus();
                        }
                    }
                } else {
                    var jsonResponse = JSON.parse(request.responseText);
                    var errorObj = document.getElementById("getissues-error");
                    var rateLimitRemaining = request.getResponseHeader("X-RateLimit-Remaining");
                    if (typeof errorObj != "undefined" && errorObj != null) {
                        errorObj.innerHTML = jsonResponse["message"];
                        errorObj.style['display'] = "";
                        if (rateLimitRemaining == 0) {
                            var rateLimitReset = request.getResponseHeader("X-RateLimit-Reset");
                            var secsToReset = rateLimitReset - (Date.now() / 1000);
                            var s = (secsToReset < 120) ? Math.ceil(secsToReset) + " seconds" : Math.ceil(secsToReset / 60) + " minutes";
                            errorObj.innerHTML += "<p>Limit will be reset in " + s;
                        }
                    }
                    return;
                    // alert("Problem retrieving XML data");
                }
            }
        }

        this.s4 = function() {
            return Math.floor((1 + Math.random()) * 0x10000).toString(16)
                .substring(1);
        }

        this.showVoteItems = function(items, hasMore) {
            var numIssues = document.getElementById("num-issues");
            if (typeof numIssues != "undefined" && numIssues != null) {
                numIssues.innerHTML = (hasMore ? "Latest " : "") + items.length;
            }

            items.sort(function(a, b) {
                return b['reactions']['+1'] - a['reactions']['+1'];
            });

            var arrayLength = items.length;
            for (var i = 0; i < arrayLength; i++) {
                var entry = items[i];

                var newID = this.templateID + entry['number'];
                var clone = document.getElementById(newID);
                if (typeof clone == 'undefined' || clone == null) {
                    var template = document.getElementById(this.templateID);
                    clone = template.cloneNode(true);
                    clone.id = newID;
                    template.parentNode.appendChild(clone);
                    clone.style['display'] = "";
                    clone.tabIndex = i;
                }
                // console.log(clone.id);

                var descendents = this.getAllElementsWithAttribute(clone, 'data-id');
                var j, e;
                for (j = 0; j < descendents.length; j++) {
                    e = descendents[j];
                    var id = e.getAttribute('data-id');

                    var idTree = id.split(".");
                    var entryItem = entry[idTree[0]];
                    var extra = "";
                    for (k = 1; k < idTree.length; k++) {
                        var part = idTree[k];

                        if (Array.isArray(entryItem)) {
                            entryArray = entryItem;
                            entryItem = "";
                            for (arrayIDX = 0; arrayIDX < entryArray.length; arrayIDX++) {
                                if (arrayIDX != 0) {
                                    entryItem += " &middot; "
                                }
                                entryItem += entryArray[arrayIDX][part];
                            }
                            break;
                        }

                        if (part.startsWith("&") || part.startsWith("?")) {
                            extra = part;
                            break;
                        }
                        entryItem = entryItem[part];
                    }

                    if (typeof entryItem != 'undefined') {
                        if (e.tagName == 'A') {
                            e.setAttribute('href', entryItem + extra);
                        } else if (e.tagName == 'IMG') {
                            e.setAttribute('src', entryItem + extra);
                        } else {
                            e.innerHTML = entryItem;
                        }
                    }
                }
            }

        }

        this.getAllElementsWithAttribute = function(root, attribute) {
            var matchingElements = [];
            var allElements = root.getElementsByTagName('*');
            for (var i = 0, n = allElements.length; i < n; i++) {
                if (allElements[i].getAttribute(attribute) !== null) {
                    // Element exists with attribute. Add to array.
                    matchingElements.push(allElements[i]);
                }
            }
            return matchingElements;
        }

        this.showLoginRequired = function(actionName) {
            alert("You need to authorize your GitHub account in order to " + actionName + ".\n\nAlternatively, you can click on the Issue title, and add the Thumbs Up reaction to the OP");
        }

        this.vote = function(targ, val = "+1") {
            if (typeof this.token === "undefined" ) {
                this.showLoginRequired("vote");
                return;
            }

            while (!targ.id.startsWith(this.templateID)) {
                targ = targ.parentNode;
                if (typeof targ == 'undefined') {
                    return;
                }
            }
            var itemID = targ.id.substr(this.templateID.length);

            var request = new XMLHttpRequest();
            request.onreadystatechange = function() {
                that.voteset_state_change(request, targ);
            };
            request.open("POST",
                "https://api.github.com/repos/" + this.gitHubPath + "/issues/" +
                itemID + "/reactions", true);
            request.setRequestHeader("X-Requested-With", 'IssueVote (OAuth App; org/BiglySoftware; user/TuxPaper; XMLHttpRequest)');
            request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            request.setRequestHeader("Accept",
                "application/vnd.github.squirrel-girl-preview,application/vnd.github.v3.html+json"
            );
            if (typeof this.token !== "undefined") {
                request.setRequestHeader("Authorization", "token " + this.token);
            }
            request.send('{ "content": "' + val + '" }');

        }

        this.unvote = function(id) {
            if (typeof this.token === "undefined" ) {
                this.showLoginRequired("un-vote");
                return;
            }
            var request = new XMLHttpRequest();
            request.onreadystatechange = function() {
                that.unvote_state_change(request);
            };
            request.open("DELETE",
                "https://api.github.com/reactions/" + id, true);
            request.setRequestHeader("X-Requested-With", 'IssueVote (OAuth App; org/BiglySoftware; user/TuxPaper; XMLHttpRequest)');
            request.setRequestHeader("Content-type",
                "application/x-www-form-urlencoded");

            request.setRequestHeader("Accept",
                "application/vnd.github.squirrel-girl-preview,application/vnd.github.v3.html+json"
            );
            if (typeof this.token !== "undefined") {
                request.setRequestHeader("Authorization", "token " + this.token);
            }
            request.send(null);
        }

        this.voteset_state_change = function(request, targ) {
            if (request.readyState == 4) { // 4 = "loaded"
                if (request.status == 401) { // Unauthorized
                    var jsonResponse = JSON.parse(request.responseText);
                    alert("Unauthorized : " + jsonResponse['message']);
                    return;
                } else if (request.status == 200) {

                    if (confirm("Already Voted!  Do you wish to un-vote it?")) {
                        var jsonResponse = JSON.parse(request.responseText);
                        this.unvote(jsonResponse['id']);
                        return;
                    }
                }
                // console.log(request.responseText);
                this.buildIssues();
            }
        }

        this.unvote_state_change = function(request) {
            if (request.readyState == 4) { // 4 = "loaded"
                if (request.status == 204) {
                    // ok
                }
                // console.log(request.responseText);
                this.buildIssues();
            }
        }
    };

    function getAnchor() {
        var currentUrl = document.URL,
            urlParts   = currentUrl.split('#');

        return (urlParts.length > 1) ? urlParts[1] : null;
    }

    //githubPath = "BiglySoftware/BiglyBT";
    githubPath = "QuantiModo/quantimodo-android-chrome-ios-web-app";
    githubIssueVote.init(githubPath, "Issue", "enhancement", "");
    githubIssueVote.buildIssues();

</script>
<div class="content_wrapper">
    <div class="container">
        <div class="noalign">
            <h1>Vote for Issues for {{ app_display_name() }}
                <div style="font: initial"> Help Us Prioritize By Voting
                </div>
            </h1>


            This is a very simple voting page for GitHub issues. To create an issue, please use
            <a href="https://www.github.com/BiglySoftware/BiglyBT/issues">BiglyBT Issues</a>
            page.
            <p>
                <a class="btn" href="?action=login">Authorize your GitHub account</a>
                to vote.
            </p>
            <br> <span id="num-issues">42</span> Open Issues
        </div>
        <div id="getissues-loading" style="display: none;"><img src="https://static.biglybt.com/img/spinner.gif"></div>
        <div id="getissues-error" style="display: none"></div>
        <div id="Issue" class="item" style="display: none">
            <table>
                <tbody>
                <tr>
                    <td class="voteInfo">
                        <div class="votes">
                            <div data-id="reactions.+1"></div>
                            <div>votes</div>
                        </div>
                        <a class="votebutton" href="" onclick="githubIssueVote.vote(this); return false">Vote</a>
                    </td>
                    <td class="ideaArea">
                        <h2>
                            <a data-id="html_url"><span class="title" data-id="title"></span></a>
                        </h2>
                        <div class="voteAuthor">
                            by
                            <img data-id="user.avatar_url.&amp;s=20">
                            <span data-id="user.login"></span>
                        </div>
                        <div data-id="body_html"></div>

                        <div class="ideaBottom">
                            <span data-id="comments"></span> comments <span class="issueLabels"
                                                                            data-id="labels.name"></span>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        @foreach( \App\Repos\IonicRepo::issues(['state' => 'open', 'labels' => 'enhancement']) as $issue )
            <div id="Issue{{ $issue["number"] }}" class="item" style="" tabindex="0">
                <table>
                    <tbody>
                    <tr>
                        <td class="voteInfo">
                            <div class="votes">
                                <div data-id="reactions.+1">{{ $issue["reactions"]["+1"] }}</div>
                                <div>votes</div>
                            </div>
                            <a class="votebutton" href="" onclick="githubIssueVote.vote(this); return false">Vote</a>
                        </td>
                        <td class="ideaArea">
                            <h2>
                                <a data-id="html_url" href="{{ $issue["html_url"] }}">
                                    <span class="title" data-id="title">{{ $issue["title"] }}</span></a>
                            </h2>
                            <div class="voteAuthor">
                                by
                                <img data-id="user.avatar_url.&amp;s=20"
                                     src="{{ $issue["user"]["avatar_url"] }}">
                                <span data-id="user.login">{{ $issue["user"]["login"] }}</span>
                            </div>
                            <div data-id="body_html">
                                {{ $issue["body"] }}
                            </div>

                            <div class="ideaBottom">
                                <span data-id="comments">{{ $issue["comments"] }}</span>comments
                                <span class="issueLabels" data-id="labels.name">
                                    @foreach($issue["labels"] as $label)
                                        <a href="{{ $label["url"] }}" target="_blank">{{ $label["name"] }}</a>
                                    @endforeach
                                </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>

<div class="otherLabels">
    <div>Other labels</div>
    @foreach( \App\Repos\IonicRepo::labels() as $label)
        <a class="otherLabel" href="{{ $label['url'] }}" style="background: {{ "#".$label["color"] }}; color: #333">
            <span class="name">{{ $label['name'] }}</span></a>
    @endforeach
</div>

<div class="tip">Tip: You can vote on any GitHub project's issues, using vote.biglybt.com/&lt;full repo name&gt;, such
    as
    <a href="https://vote.biglybt.com/isaacs/github">https://vote.biglybt.com/isaacs/github</a>
</div>

@push('js')
    <script>
        var githubIssueVote = new function() {
            var that = this;
            var templateID;
            var gitHubPath;
            var token;

            this.init = function(gitHubPath, templateID, labels, token) {
                this.templateID = templateID;
                this.gitHubPath = gitHubPath;
                this.labels = labels;
                if (token != "") {
                    this.token = token;
                }
            }

            this.buildIssues = function() {
                var loadingObj = document.getElementById("getissues-loading");
                if (typeof loadingObj != "undefined" && loadingObj != null) {
                    loadingObj.style['display'] = "";
                }

                var request = new XMLHttpRequest();
                request.onreadystatechange = function() {
                    that.issues_state_change(request)
                };
                var url = "https://api.github.com/repos/" + this.gitHubPath + "/issues?state=open";
                if(this.labels){
                    url += "&labels=" + encodeURIComponent(this.labels);
                }
                url +=  "&per_page=100&cb=" + this.s4();
                request.open("GET", url, true);
                request.setRequestHeader("X-Requested-With", 'IssueVote (OAuth App; org/BiglySoftware; user/TuxPaper; XMLHttpRequest)');
                request.setRequestHeader("Accept",
                    "application/vnd.github.squirrel-girl-preview,application/vnd.github.v3.html+json"
                );
                if (typeof this.token !== "undefined") {
                    request.setRequestHeader("Authorization", "token " + this.token);
                }
                request.send(null);
            };

            this.issues_state_change = function(request) {
                if (request.readyState == 4) { // 4 = "loaded"
                    var loadingObj = document.getElementById("getissues-loading");
                    if (typeof loadingObj != "undefined" && loadingObj != null) {
                        loadingObj.style['display'] = "none";
                    }
                    if (request.status == 200) {
                        // console.log(request.responseText);
                        var jsonResponse = JSON.parse(request.responseText);
                        var hasMore = request.getResponseHeader("Link") !== null;
                        this.showVoteItems(jsonResponse, hasMore);
                        // Normally we don't need to do this, as an achor will focus a div
                        // with a tabIndex.  However, we are adding divs dynamically, so we
                        // need to explicitly focus
                        var anchor = getAnchor();
                        if (anchor != null) {
                            var focusObj = document.getElementById(anchor);
                            if (typeof focusObj != "undefined" && focusObj != null) {
                                focusObj.focus();
                            }
                        }
                    } else {
                        var jsonResponse = JSON.parse(request.responseText);
                        var errorObj = document.getElementById("getissues-error");
                        var rateLimitRemaining = request.getResponseHeader("X-RateLimit-Remaining");
                        if (typeof errorObj != "undefined" && errorObj != null) {
                            errorObj.innerHTML = jsonResponse["message"];
                            errorObj.style['display'] = "";
                            if (rateLimitRemaining == 0) {
                                var rateLimitReset = request.getResponseHeader("X-RateLimit-Reset");
                                var secsToReset = rateLimitReset - (Date.now() / 1000);
                                var s = (secsToReset < 120) ? Math.ceil(secsToReset) + " seconds" : Math.ceil(secsToReset / 60) + " minutes";
                                errorObj.innerHTML += "<p>Limit will be reset in " + s;
                            }
                        }
                        return;
                        // alert("Problem retrieving XML data");
                    }
                }
            }

            this.s4 = function() {
                return Math.floor((1 + Math.random()) * 0x10000).toString(16)
                    .substring(1);
            }

            this.showVoteItems = function(items, hasMore) {
                var numIssues = document.getElementById("num-issues");
                if (typeof numIssues != "undefined" && numIssues != null) {
                    numIssues.innerHTML = (hasMore ? "Latest " : "") + items.length;
                }

                items.sort(function(a, b) {
                    return b['reactions']['+1'] - a['reactions']['+1'];
                });

                var arrayLength = items.length;
                for (var i = 0; i < arrayLength; i++) {
                    var entry = items[i];

                    var newID = this.templateID + entry['number'];
                    var clone = document.getElementById(newID);
                    if (typeof clone == 'undefined' || clone == null) {
                        var template = document.getElementById(this.templateID);
                        clone = template.cloneNode(true);
                        clone.id = newID;
                        template.parentNode.appendChild(clone);
                        clone.style['display'] = "";
                        clone.tabIndex = i;
                    }
                    // console.log(clone.id);

                    var descendents = this.getAllElementsWithAttribute(clone, 'data-id');
                    var j, e;
                    for (j = 0; j < descendents.length; j++) {
                        e = descendents[j];
                        var id = e.getAttribute('data-id');

                        var idTree = id.split(".");
                        var entryItem = entry[idTree[0]];
                        var extra = "";
                        for (k = 1; k < idTree.length; k++) {
                            var part = idTree[k];

                            if (Array.isArray(entryItem)) {
                                entryArray = entryItem;
                                entryItem = "";
                                for (arrayIDX = 0; arrayIDX < entryArray.length; arrayIDX++) {
                                    if (arrayIDX != 0) {
                                        entryItem += " &middot; "
                                    }
                                    entryItem += entryArray[arrayIDX][part];
                                }
                                break;
                            }

                            if (part.startsWith("&") || part.startsWith("?")) {
                                extra = part;
                                break;
                            }
                            entryItem = entryItem[part];
                        }

                        if (typeof entryItem != 'undefined') {
                            if (e.tagName == 'A') {
                                e.setAttribute('href', entryItem + extra);
                            } else if (e.tagName == 'IMG') {
                                e.setAttribute('src', entryItem + extra);
                            } else {
                                e.innerHTML = entryItem;
                            }
                        }
                    }
                }

            }

            this.getAllElementsWithAttribute = function(root, attribute) {
                var matchingElements = [];
                var allElements = root.getElementsByTagName('*');
                for (var i = 0, n = allElements.length; i < n; i++) {
                    if (allElements[i].getAttribute(attribute) !== null) {
                        // Element exists with attribute. Add to array.
                        matchingElements.push(allElements[i]);
                    }
                }
                return matchingElements;
            }

            this.showLoginRequired = function(actionName) {
                alert("You need to authorize your GitHub account in order to " + actionName + ".\n\nAlternatively, you can click on the Issue title, and add the Thumbs Up reaction to the OP");
            }

            this.vote = function(targ, val = "+1") {
                if (typeof this.token === "undefined" ) {
                    this.showLoginRequired("vote");
                    return;
                }

                while (!targ.id.startsWith(this.templateID)) {
                    targ = targ.parentNode;
                    if (typeof targ == 'undefined') {
                        return;
                    }
                }
                var itemID = targ.id.substr(this.templateID.length);

                var request = new XMLHttpRequest();
                request.onreadystatechange = function() {
                    that.voteset_state_change(request, targ);
                };
                request.open("POST",
                    "https://api.github.com/repos/" + this.gitHubPath + "/issues/" +
                    itemID + "/reactions", true);
                request.setRequestHeader("X-Requested-With", 'IssueVote (OAuth App; org/BiglySoftware; user/TuxPaper; XMLHttpRequest)');
                request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                request.setRequestHeader("Accept",
                    "application/vnd.github.squirrel-girl-preview,application/vnd.github.v3.html+json"
                );
                if (typeof this.token !== "undefined") {
                    request.setRequestHeader("Authorization", "token " + this.token);
                }
                request.send('{ "content": "' + val + '" }');

            }

            this.unvote = function(id) {
                if (typeof this.token === "undefined" ) {
                    this.showLoginRequired("un-vote");
                    return;
                }
                var request = new XMLHttpRequest();
                request.onreadystatechange = function() {
                    that.unvote_state_change(request);
                };
                request.open("DELETE",
                    "https://api.github.com/reactions/" + id, true);
                request.setRequestHeader("X-Requested-With", 'IssueVote (OAuth App; org/BiglySoftware; user/TuxPaper; XMLHttpRequest)');
                request.setRequestHeader("Content-type",
                    "application/x-www-form-urlencoded");

                request.setRequestHeader("Accept",
                    "application/vnd.github.squirrel-girl-preview,application/vnd.github.v3.html+json"
                );
                if (typeof this.token !== "undefined") {
                    request.setRequestHeader("Authorization", "token " + this.token);
                }
                request.send(null);
            }

            this.voteset_state_change = function(request, targ) {
                if (request.readyState == 4) { // 4 = "loaded"
                    if (request.status == 401) { // Unauthorized
                        var jsonResponse = JSON.parse(request.responseText);
                        alert("Unauthorized : " + jsonResponse['message']);
                        return;
                    } else if (request.status == 200) {

                        if (confirm("Already Voted!  Do you wish to un-vote it?")) {
                            var jsonResponse = JSON.parse(request.responseText);
                            this.unvote(jsonResponse['id']);
                            return;
                        }
                    }
                    // console.log(request.responseText);
                    this.buildIssues();
                }
            }

            this.unvote_state_change = function(request) {
                if (request.readyState == 4) { // 4 = "loaded"
                    if (request.status == 204) {
                        // ok
                    }
                    // console.log(request.responseText);
                    this.buildIssues();
                }
            }
        };

        function getAnchor() {
            var currentUrl = document.URL,
                urlParts   = currentUrl.split('#');

            return (urlParts.length > 1) ? urlParts[1] : null;
        }

        //githubPath = "BiglySoftware/BiglyBT";
        githubPath = "QuantiModo/quantimodo-android-chrome-ios-web-app";
        githubIssueVote.init(githubPath, "Issue", "enhancement", "");
        githubIssueVote.buildIssues();

    </script>
@endpush