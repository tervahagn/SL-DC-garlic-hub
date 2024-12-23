document.addEventListener("DOMContentLoaded", function(event)
{
	const tree = new mar10.Wunderbaum({
		element: document.getElementById("mediapool-tree"),
		source: { url: '/async/mediapool/node/0' },
		init: (e) => {
		},
		lazyLoad: function (e) {
			return { url: '/async/mediapool/node/'+ e.node.key, params: { parentKey: e.node.key } };
		},
		activate: (e) => {
			document.getElementById("current-path").textContent = "/" + e.node.getPath();
		}
	});

	const editFolderDialog = document.getElementById('editFolderDialog');
	const addRootFolder = document.getElementById('addRootFolder');

	addRootFolder.addEventListener('click', () => {
		editFolderDialog.showModal();
	});

	editFolderDialog.addEventListener('close', () => {
		console.log('Dialog closed with value:', dialog.returnValue);
	});

});