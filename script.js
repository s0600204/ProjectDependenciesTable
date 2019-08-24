
var server = {

	fetch: function (args, cb) {
		if (cb === undefined || typeof(cb) !== "function") {
			console.error("This is not a valid callback");
		}
		request_args = this._populateArgs(args);
		this._http_request(request_args, cb);
	},

	_populateArgs: function (args) {
		try {
			request_args = new FormData();
			for (let arg in args)
			{
				if (Array.isArray(args[arg])) {
					for (let subArg in args[arg]) {
						request_args.append(arg+"[]", args[arg][subArg]);
					}
				} else {
					request_args.append(arg, args[arg]);
				}
			}
		} catch (err) {
			console.error("I'm sorry, but your browser is not capable of handling this. Please update your browser.");
			throw err;
		}
		return request_args;
	},

	_http_request: function (request_args, callback) {

		let http_request = new XMLHttpRequest();
		http_request.onreadystatechange = function () {
			if (http_request.readyState === 4) {
				if (http_request.status === 200) {
					//try {
						callback(JSON.parse(http_request.responseText));
					//} catch (e) {
					//	console.error("Hmm... something went wrong. Try again later, and hopefully I'll be fixed!");
					//	console.log(http_request.responseText);
					//}
				} else {
					console.error('There was a problem with the request.');
				}
			}
		};
		http_request.open('POST', './request.php', true);
		http_request.send(request_args);
	}
};

function init()
{
	for (let dependency of g_DependencyList)
	{
		let args = {
			'dep_code': dependency
		}
		if (g_DependencyMinReqs[dependency])
			args['min_req'] = g_DependencyMinReqs[dependency];
		server.fetch(args, populate_versions);
	}
}

function populate_versions(version_info)
{
	if (!version_info.status)
	{
		console.error("Loading "+version_info.code+" failed");
		return;
	}

	let header_cell = document.getElementById('dep__' + version_info.code);
	if (g_DependencyAlts[version_info.code])
		header_cell = document.getElementById('dep__' + g_DependencyAlts[version_info.code]);
	let column = header_cell.cellIndex + header_cell.parentElement.children[0].colSpan;

	let versions = version_info.versions;
	let latest = version_info.latestVersion;
	for (let distro of g_DistroList)
	{
		let row = document.getElementById('distro__' + distro);
		let col = column - 2;
		if (row.children[0].rowSpan > 1)
			++col;

		let cell = row.children[col];
		let altdeps = cell.querySelector('.altdeps');

		if (g_DependencyAlts[version_info.code] && !altdeps)
			continue;

		let new_element = create_version_element(
			versions[distro] || '-',
			version_info.states[distro] || 'notavailable'
		);

		if (!g_DependencyAlts[version_info.code])
			cell.insertBefore(new_element, altdeps);
		else
			altdeps.appendChild(new_element);
	}
}

function create_version_element(version_text, status_class)
{
	let vers_elem = document.importNode(document.getElementById('version_template').content, true);
	vers_elem.children[0].classList.add(status_class);
	vers_elem.children[0].children[0].innerText = version_text;
	return vers_elem;
}

function toggleBoxes(selected) {
	let boxes = ['infobox', 'projectsbox'];
	for (let boxid of boxes)
	{
		box_elem = document.getElementById(boxid);
		box_elem.style.display = (selected == boxid && window.getComputedStyle(box_elem).display === "none") ?  "block" : "none";
	}
}
