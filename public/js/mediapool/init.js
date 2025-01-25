import { NodesModel } from "./treeview/NodesModel.js";
import { DirectoryView } from "./treeview/DirectoryView.js";
import { TreeDialog } from "./treeview/TreeDialog.js";

import { UploaderDialog } from "./uploads/UploaderDialog.js";
import { LocalFilesPreviews } from "./uploads/Local/LocalFilesPreviews.js";
import { DragDropManager } from "./uploads/Local/DragDropManager.js";
import { PreviewFactory } from "./uploads/Preview/PreviewFactory.js";
import { FetchClient } from "../core/FetchClient.js";
import { MediaList } from "./media/MediaList.js";
import { MediaService } from "./media/MediaService.js";
import { ContextMenuMediaFactory } from "./media/ContextMenuMediaFactory.js";
import { MediaFactory } from "./media/MediaFactory.js";
import { MediaDialog } from "./media/MediaDialog.js";

import { LocalFilesElements } from "./uploads/Local/LocalFilesElements.js";
import { LocalFilesUploader } from "./uploads/Local/LocalFilesUploader.js";

import { ExternalFileUploader } from "./uploads/External/ExternalFileUploader.js";
import { ExternalFileElements } from "./uploads/External/ExternalFileElements.js";

import { SpicyCam } from "./uploads/Webcam/SpicyCam.js";
import {WebcamElements} from "./uploads/Webcam/WebcamElements.js";
import { WebcamUploader } from "./uploads/Webcam/WebcamUploader.js";
import { WebcamPreviews } from "./uploads/Webcam/WebcamPreviews.js";


document.addEventListener("DOMContentLoaded", function()
{
	const nodesModel    = new NodesModel();
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

	// Section for local file uploads
	const localFilesElements = new LocalFilesElements();
    const previewFactory     = new PreviewFactory();
    const filePreviews       = new LocalFilesPreviews(localFilesElements.dropzonePreview, previewFactory);
	const dragDropManager    = new DragDropManager(localFilesElements, filePreviews);
	dragDropManager.init();
	const localFilesUploader = new LocalFilesUploader(
		filePreviews,
		localFilesElements,
		directoryView,
		uploaderDialog,
		fetchClient
	);
    filePreviews.setFileUploader(localFilesUploader);

	// Section for external file uploads
    const externalFileUploader = new ExternalFileUploader(
        new ExternalFileElements(),
        directoryView,
        uploaderDialog,
        fetchClient
    );

	// Section for uploads from camera / webcam
    const webcamElements = new WebcamElements();
    const webcamPreviews = new WebcamPreviews(webcamElements.previewRecordsArea, previewFactory);
    const webcamUploader = new WebcamUploader(
        new SpicyCam(webcamElements.webcamVideo),
        webcamPreviews,
        webcamElements,
        directoryView,
        uploaderDialog,
        fetchClient
    );
    webcamPreviews.setFileUploader(webcamUploader);

});

