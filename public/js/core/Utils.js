export class Utils
{
	static formatSecondsToTime(seconds)
	{
		const hours = Math.floor(seconds / 3600);
		const minutes = Math.floor((seconds % 3600) / 60);
		const secs = seconds % 60;

		const pad = (num) => String(num).padStart(2, '0');

		return `${pad(hours)}:${pad(minutes)}:${pad(secs)}`;
	}

	static formatBytes(bytes)
	{
		const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

		if (bytes === 0) return '0 Bytes';

		const i = Math.floor(Math.log(bytes) / Math.log(1024));
		const size = (bytes / Math.pow(1024, i)).toFixed(2);

		return `${size} ${sizes[i]}`;
	}
}