import { NodesModel } from "./treeview/NodesModel.js";
import { DirectoryView } from "./treeview/DirectoryView.js";
import { TreeDialog } from "./treeview/TreeDialog.js";

import { UploaderDialog } from "./uploads/UploaderDialog.js";
import { FilePreviews } from "./uploads/FilePreviews.js";
import { DragDropManager } from "./uploads/DragDropManager.js";
import { PreviewFactory } from "./uploads/Preview/PreviewFactory.js";
import { FileUploader } from "./uploads/FileUploader.js";
import { FetchClient } from "../core/FetchClient.js";
import { MediaList } from "./media/MediaList.js";
import { MediaService } from "./media/MediaService.js";
import { ContextMenuMediaFactory } from "./media/ContextMenuMediaFactory.js";

document.addEventListener("DOMContentLoaded", function(event)
{
	// treeview section
	let nodesModel    = new NodesModel();
	let fetchClient   = new FetchClient();
	let mediaService  = new MediaService(fetchClient);
	let mediaList     = new MediaList(
		document.getElementById("media-list"),
		document.getElementById('media-template'),
		new ContextMenuMediaFactory(document.getElementById('context_menu_media'), fetchClient, null)
	);

	let directoryView  = new DirectoryView(
		document.getElementById("mediapool-tree"),
		document.getElementById("current-path"),
		mediaList,
		mediaService
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

	const startFileUpload = document.getElementById("startFilesUpload");
	const filePreviews = new FilePreviews(
		document.getElementById('dropzone-preview'),
		startFileUpload,
		new PreviewFactory()
	)
	const dropzone = document.getElementById('dropzone');
	const dragDropManager = new DragDropManager(
		dropzone,
		filePreviews
	);
	dragDropManager.init();

	dropzone.addEventListener('click', () => fileInput.click());
	const fileInput = document.getElementById('fileInput');
	fileInput.addEventListener('change', (event) => {
		const files = event.target.files;
		filePreviews.handleFiles(files);
	});

	const fileUploader = new FileUploader(
		directoryView,
		filePreviews,
		uploaderDialog,
		fetchClient
	);
	fileUploader.initFileUpload(startFileUpload);


});

