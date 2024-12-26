document.addEventListener("DOMContentLoaded", function(event)
{
	let nodesModel     = new NodesModel();
	const tree_element = document.getElementById("mediapool-tree");
	let directoryView  = new DirectoryView(tree_element);
	directoryView.addFilter(document.getElementById("tree_filter"));

	let treeDialog = new TreeDialog(
		document.getElementById('editFolderDialog'),
		document.getElementById("close_dialog_button"),
		directoryView,
		nodesModel
	);

	document.getElementById('addRootFolder').addEventListener('click', () => {
		treeDialog.setCurrentNode(null);
		treeDialog.prepareShow("add_root_folder", lang);
		treeDialog.show();
	});


	tree_element.addEventListener("contextmenu", (event) => {
		event.preventDefault();
		currentTreeNode = directoryView.setActiveNodeFromEventTarget(event.target);

		const menu = document.querySelector('#context_menu_tree').content.cloneNode(true).firstElementChild;
		let contextMenu    = new ContextMenu(menu, nodesModel, treeDialog);
		contextMenu.show(event);

		const editNodeElement = document.getElementById("edit_node");
		contextMenu.addEditEvent(editNodeElement, currentTreeNode, lang);

		const addNodeElement = document.getElementById("add_node");
		contextMenu.addAddEvent(addNodeElement, currentTreeNode, lang);

		const removeNodeElement = document.getElementById("remove_node");
		contextMenu.addRemoveEvent(removeNodeElement, currentTreeNode);
	});
});

