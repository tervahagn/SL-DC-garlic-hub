import { NodesModel } from "./treeview/NodesModel.js";
import { DirectoryView } from "./treeview/DirectoryView.js";
import { TreeDialog } from "./treeview/TreeDialog.js";

import { UploaderDialog } from "./uploads/UploaderDialog.js";
import { FilePreviews } from "./uploads/FilePreviews.js";
import { DragDropManager } from "./uploads/DragDropManager.js";
import { PreviewFactory } from "./uploads/Preview/PreviewFactory.js";
import { FileUploader } from "./uploads/FileUploader.js";

document.addEventListener("DOMContentLoaded", function(event)
{
	// treview section
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

	// upload section

	const uploaderDialog = new UploaderDialog(
		document.getElementById('uploaderDialog'),
		document.getElementById('openUploadDialog'),
		document.getElementById('closeDialog'),
		document.getElementById("closeUploadDialog")
	);
	uploaderDialog.init();

	const filePreviews = new FilePreviews(
		document.getElementById('dropzone-preview'),
		new PreviewFactory()
	)
	const dragDropManager = new DragDropManager(
		document.getElementById('dropzone'),
		filePreviews
	);
	dragDropManager.init();

	const fileUploader = new FileUploader(
		'#dragDropTab .upload-button',
		filePreviews.getFileList()
	);
	fileUploader.init();

});

