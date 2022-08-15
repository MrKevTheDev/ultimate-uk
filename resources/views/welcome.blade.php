<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/mithril/3.0.0-next.8/mithril.min.js"></script>
</head>
<body class="antialiased">
<div id="app"
     class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">

</div>
</body>
<script type="text/javascript">
    var root = document.getElementById('app')


    let Fform = {
        data: {
            data: []
        },
        newContact: {
            name: '',
            phone: '',
            email: '',
        },
        submit: () => {

            return m.request({
                    method: "POST",
                    url: "/api/contact/",

                    body: {
                        name: Fform.newContact.name,
                        phone: Fform.newContact.phone,
                        email: Fform.newContact.email,
                    }
                }
            ).then((r) => {
                    console.log(r)
                    Fform.data = r
                }
            );
        },
        oninit: () => {
            Fform.getContent()
        },

        getContent: () => {
            m.request({
                    method: "GET",
                    url: "/api/contact/"
                }
            ).then((r) => {
                    console.log(r)
                    Fform.data = r
                }
            );
            return false;
        },
        view: () => {
            return m("div", {"class": "p-10 w-full grid grid-cols-2 gap-5"},
                [

                    m("form", {
                            "class": "", onsubmit: () => {
                                Fform.submit()
                                return false;
                            }
                        },
                        [
                            m("div", {"class": "flex flex-wrap -mx-3 mb-6"},
                                m("div", {"class": "w-full px-3"},
                                    [
                                        m("label", {
                                                "class": "block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2",
                                                "for": "name"
                                            },
                                            " Name "
                                        ),
                                        m("input", {
                                            "class": "appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",
                                            "id": "name",
                                            required: true,
                                            "type": "text",
                                            value: Fform.newContact.name,
                                            onchange: function (events) {
                                                Fform.newContact.name = events.target.value;
                                            }
                                        }),

                                    ]
                                )
                            ),
                            m("div", {"class": "flex flex-wrap -mx-3 mb-6"},
                                m("div", {"class": "w-full px-3"},
                                    [
                                        m("label", {
                                                "class": "block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2",
                                                "for": "grid-password"
                                            },
                                            " E-mail "
                                        ),
                                        m("input", {
                                            "class": "appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",
                                            "id": "email",
                                            "type": "email",
                                            required: true,
                                            value: Fform.newContact.email,
                                            onchange: function (events) {
                                                Fform.newContact.email = events.target.value;
                                            }
                                        }),
                                    ]
                                )
                            ),
                            m("div", {"class": "flex flex-wrap -mx-3 mb-6"},
                                m("div", {"class": "w-full px-3"},
                                    [
                                        m("label", {
                                                "class": "block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2",
                                                "for": "grid-password"
                                            },
                                            " phone"
                                        ),
                                        m("input", {
                                            "class": "appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500",
                                            "id": "email",
                                            "type": "phone",
                                            value: Fform.newContact.phone,
                                            required: true,
                                            onchange: function (events) {
                                                Fform.newContact.phone = events.target.value;
                                            }
                                        }),

                                    ]
                                )
                            ),

                            m("div", {"class": "md:flex md:items-center"},
                                [
                                    m("div", {"class": "md:w-1/3"},
                                        m("button", {
                                                "class": "shadow bg-teal-400 hover:bg-teal-400 focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4 rounded",
                                                "type": "submit",
                                            },
                                            " Add Contact "
                                        )
                                    ),
                                    m("div", {"class": "md:w-2/3"})
                                ]
                            )
                        ],
                    ),
                    m("div", {"class": "flex flex-col"},
                        m("div", {"class": "overflow-x-auto sm:-mx-6 lg:-mx-8"},
                            m("div", {"class": "py-2 inline-block min-w-full sm:px-6 lg:px-8"},
                                m("div", {"class": "overflow-hidden"},
                                    m("table", {"class": "min-w-full"},
                                        [
                                            m("thead", {"class": "bg-white border-b"},
                                                m("tr",
                                                    [
                                                        m("th", {
                                                                "class": "text-sm font-medium text-gray-900 px-6 py-4 text-left",
                                                                "scope": "col"
                                                            },
                                                        ),
                                                        m("th", {
                                                                "class": "text-sm font-medium text-gray-900 px-6 py-4 text-left",
                                                                "scope": "col"
                                                            },
                                                            " Name "
                                                        ),
                                                        m("th", {
                                                                "class": "text-sm font-medium text-gray-900 px-6 py-4 text-left",
                                                                "scope": "col"
                                                            },
                                                            " Phone "
                                                        ),
                                                        m("th", {
                                                                "class": "text-sm font-medium text-gray-900 px-6 py-4 text-left",
                                                                "scope": "col"
                                                            },
                                                            " Email "
                                                        )
                                                    ]
                                                )
                                            ),
                                            m("tbody",
                                                Fform.data.data.map((contact) => {
                                                    return m("tr", {"class": "bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100"},
                                                        [
                                                            m("td", {"class": "px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"},
                                                                contact.id
                                                            ),
                                                            m("td", {"class": "text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap"},
                                                                contact.name
                                                            ),
                                                            m("td", {"class": "text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap"},
                                                                contact.phone
                                                            ),
                                                            m("td", {"class": "text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap"},
                                                                contact.email
                                                            )
                                                        ]
                                                    )
                                                })
                                            )
                                        ]
                                    ),
                                    m(Paginated, {data: Fform.data})
                                )
                            )
                        )
                    )

                ]
            )


        }
    };
    let Paginated = {
        pagination: {},
        oninit: (vnod) => {

            Paginated.pagination = Fform.data.pagination;
            console.log(vnod.attrs, Paginated.pagination)
        },
        dopageanaton: () => {

        },
        view: () => {
            return m("nav", {
                    "class": "relative z-0 inline-flex rounded-md shadow-sm -space-x-px",
                    "aria-label": "Pagination"
                },
                [
                    m("a", {
                            "class": "relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50",
                            "href": "#"
                        },
                        [
                            m("span", {"class": "sr-only"},
                                "Previous"
                            ),
                            m("svg", {
                                    "class": "h-5 w-5",
                                    "xmlns": "http://www.w3.org/2000/svg",
                                    "viewBox": "0 0 20 20",
                                    "fill": "currentColor",
                                    "aria-hidden": "true"
                                },
                                m("path", {
                                    "fill-rule": "evenodd",
                                    "d": "M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z",
                                    "clip-rule": "evenodd"
                                })
                            )
                        ]
                    ),
                    m("a", {
                            "class": "z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium",
                            "href": "#",
                            "aria-current": "page"
                        },
                        " 1 "
                    ),

                    m("a", {
                            "class": "relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50",
                            "href": "#"
                        },
                        [
                            m("span", {"class": "sr-only"},
                                "Next"
                            ),
                            m("svg", {
                                    "class": "h-5 w-5",
                                    "xmlns": "http://www.w3.org/2000/svg",
                                    "viewBox": "0 0 20 20",
                                    "fill": "currentColor",
                                    "aria-hidden": "true"
                                },
                                m("path", {
                                    "fill-rule": "evenodd",
                                    "d": "M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z",
                                    "clip-rule": "evenodd"
                                })
                            )
                        ]
                    )
                ]
            )
        }
    }
    m.route(document.body, "/", {
        "/": Fform
    })


</script>
</html>
