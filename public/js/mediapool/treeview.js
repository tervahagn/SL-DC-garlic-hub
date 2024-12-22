document.addEventListener("DOMContentLoaded", function(event)
{
	const tree = new mar10.Wunderbaum({
		element: document.getElementById("mediapool-tree"),
		source: { url: '/async/mediapool/node/0' },
		init: (e) => {
			e.tree.setFocus();
		},
		lazyLoad: function (e) {
			return { url: '/async/mediapool/node/'+ e.node.key, params: { parentKey: e.node.key } };
		},
		activate: (e) => {
			document.getElementById("current-path").textContent = "/" + e.node.getPath();
		}
	});

});