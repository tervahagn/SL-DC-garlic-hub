document.addEventListener("DOMContentLoaded", function(event)
{
	let nodesModel     = new NodesModel();
	let directoryView  = new DirectoryView(
		document.getElementById("mediapool-tree"),
		document.getElementById("current-path")
	);
	directoryView.addFilter(document.getElementById("tree_filter"));

	let treeDialog = new TreeDialog(
		document.getElementById('editFolderDialog'),
		document.getElementById("closeEditDialog"),
		directoryView,
		nodesModel
	);
	directoryView.addContextMenu(nodesModel, treeDialog, lang);

	document.getElementById('addRootFolder').addEventListener('click', () => {
		treeDialog.setCurrentNode(null);
		treeDialog.prepareShow("add_root_folder", lang);
		treeDialog.show();
	});
});

