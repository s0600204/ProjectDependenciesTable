
Project Dependencies Table
==========================

Based on data supplied via the Repology_ and PyPI_ APIs.

An example of the end result can be found at: http://s06eye.co.uk/deps.


Guide
-----

The script reads from several files, which may be found in the data subdirectory:

* ``data/distros.json`` - defines which distributions should be listed
* ``data/projects/{project_name}.json`` - lists the dependencies of a given project


``{project_name}.json``
'''''''''''''''''''''''

Within each ``{project_name}.json`` data file, dependencies are defined like so::

	{
		"name": "<name>",
		"code": "<code>"
	}

where ``<name>`` is the name of the dependency, and ``<code>`` is the code that Repology_ uses to identify the relevant package (in many cases both ``<name>`` and ``<code>`` may be identical).

These dependency definitions are not top-level objects, however: they are grouped like so::

	"dependencies": {
		"<section_name>": [
			{
				"name": "<name>",
				"code": "<code>"
			}, {
				...
			}
		]
	}

where ``<section_name>`` is the name of a section, such as "main", "v1.x", or "make".

If your project requires a minimum version of a dependency, this may be set by the use of the optional ``minRequired`` key, thusly::

	{
		"name": "gcc",
		"code": "gcc",
		"minRequired": "4.8.1"
	}

Sometimes there may be dependencies that are packaged differently in different repositories. For instance, ``libnspr4`` is commonly packaged in its own package within many repositories. However there are some cases where it is bundled up with other libraries that all originate from the same codebase.

To express this, a definition of the ``nspr`` dependency might look like this, where the "alt-" prefixed keys detail the alternate package::

	{
		"name": "libnspr4",
		"code": "nspr",
		"alt-name": ["mozilla-nss"],
		"alt-code": ["nss"]
	},

There is an optional key - ``always-show-alt`` - which if provided and set to ``true`` will cause the alternate dependency to be displayed for *all* distributions in the final table. If it is not provided, or is set to its default value of ``false``, the alternate dependency will only appear for those repositories where it has been explicitly flagged that the alternate dependency is needed.


``distros.json``
''''''''''''''''

Within the distros file, there are two ways of defining distributions, and the one to use is dependant on whether the distribution in question is a rolling release (e.g. Arch, Gentoo), or one with discrete releases (e.g. Debian, Ubuntu, Fedora).

For a rolling release type distro, the definition would look something like the following::

	{
		"name": "ArchLinux",
		"code": "arch",
		"link": "https://www.archlinux.org/packages",
		"eol": "inf",
		"alt": ["tex-gyre"]
	}

Whilst for distributions that release in discrete iterations, it looks something like the following::

	{
		"name": "Ubuntu",
		"link": "https://wiki.ubuntu.com/Releases",
		"releases": [{
			"name": "Xenial (16.04)",
			"code": "ubuntu_16_04",
			"eol": "2021-04",
			"link": "https://packages.ubuntu.com/xenial/",
			"alt": ["libglvnd"]
		}, {
			"name": "Disco (19.04)",
			"code": "ubuntu_19_04",
			"eol": "2020-01",
			"link": "https://packages.ubuntu.com/disco/"
		}, {
			"name": "Eoan (19.10)",
			"code": "ubuntu_19_10",
			"link": "https://packages.ubuntu.com/eoan/"
		}, {
			"name": "0 A.D. PPA",
			"link": "https://launchpad.net/~wfg/+archive/ubuntu/0ad.dev/+packages",
			"eol": "inf",
			"hard": {
				"boost": "1.58",
				"enet": "1.3.12",
				"gloox": "1.0.20",
				"libsodium": "1.0.16"
			}
		}]
	}

``name``
	Required.

	The name of the specific distribution or a particular release

``code``
	Required if ``hard`` is not defined.

	The code with which Repology_ identifies the distribution or particular release

``link``
	Optional.

	A useful link. Recommended to either be to the distribution or release's own list of packages provided, or to a page listing releases of the current distribution.

``eol``
	Optional.

	The predicted end-of-life of a release, if known.

	The value should either be a date in ISO-8601 extended format (``yyyy``, ``yyyy-mm``, or ``yyyy-mm-dd``), or the string "``inf``" to indicate a release with no end-of-life.

``alt``
	Optional.

	If it is known that a distro's repository uses an alternative package for a given dependency (see the section on "alt-" prefixed keys in {project_name}.json_ above), then this may be flagged by adding the (non-alternate) dependency's code to the list here.

``hard``
	Optional.

	For repositories that are not read by Repology_ (for instance a project-specific PPA_), current versions of provided packages may be stated here.



Known or Potential Issues
-------------------------

Server load
	For now, the Repology_ does not charge for its use, enforce usage limits, nor require the use of API keys. I don't know what (if any) load balancing the Repology_ service uses, so I'm not sure how much this page stresses the Repology_ service. If the number of requests exceed the point where its maintainers are happy, then this might change.



.. _Repology: https://repology.org/
.. _PyPI: https://pypi.org/
.. _PPA: https://help.launchpad.net/Packaging/PPA
