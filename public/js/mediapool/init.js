import { NodesModel } from "./treeview/NodesModel.js";
import { DirectoryView } from "./treeview/DirectoryView.js";
import { TreeDialog } from "./treeview/TreeDialog.js";

import { UploaderDialog } from "./uploads/UploaderDialog.js";
import { FilePreviews } from "./uploads/FilePreviews.js";
import { DragDropManager } from "./uploads/DragDropManager.js";
import { PreviewFactory } from "./uploads/Preview/PreviewFactory.js";
import { FetchClient } from "../core/FetchClient.js";
import { MediaList } from "./media/MediaList.js";
import { MediaService } from "./media/MediaService.js";
import { ContextMenuMediaFactory } from "./media/ContextMenuMediaFactory.js";
import { MediaFactory } from "./media/MediaFactory.js";
import { MediaDialog } from "./media/MediaDialog.js";

import { FileUploader } from "./uploads/FileUploader.js";
import { ExternalFileUploader } from "./uploads/ExternalFileUploader.js";

import { Webcam } from "./uploads/Webcam/Webcam.js";
import {WebcamElements} from "./uploads/Webcam/WebcamElements.js";
import { WebcamUploader } from "./uploads/Webcam/WebcamUploader.js";

document.addEventListener("DOMContentLoaded", function()
{
	let nodesModel    = new NodesModel();
	let fetchClient   = new FetchClient();
	let mediaService  = new MediaService(fetchClient);
	let mediaDialog   = new MediaDialog(
		document.getElementById('editMediaDialog'),
		document.getElementById("closeEditMediaDialog"),
		fetchClient
	)

	let mediaList     = new MediaList(
		document.getElementById("media-list"),
		new MediaFactory(document.getElementById('media-template'), fetchClient),
        new ContextMenuMediaFactory(document.getElementById('media-contextmenu-template'), mediaDialog, fetchClient),
	);

	let directoryView  = new DirectoryView(
		document.getElementById("mediapool-tree"),
		document.getElementById("current-path"),
		mediaList,
		mediaService,
        fetchClient
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

	const uploaderDialog = new UploaderDialog(
		document.getElementById('uploaderDialog'),
		document.getElementById('openUploadDialog'),
		document.getElementById('closeDialog'),
		document.getElementById("closeUploadDialog")
	);
	uploaderDialog.init(directoryView);

    const filePreviews = new FilePreviews(
        document.getElementById('dropzone-preview'),
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
        document.getElementById("startFilesUpload"),
		uploaderDialog,
		fetchClient
	);
    filePreviews.setFileUploader(fileUploader);

    const externalFileUploader = new ExternalFileUploader(
        document.getElementById("externalLinkField"),
        document.getElementById("startExternalFileUpload"),
        directoryView,
        uploaderDialog,
        fetchClient
    );

    const webcamElements = new WebcamElements();
    const webcamUploader = new WebcamUploader(
        new Webcam(webcamElements.getWebcamVideo()),
        webcamElements,
        directoryView,
        uploaderDialog,
        fetchClient
    );

});

