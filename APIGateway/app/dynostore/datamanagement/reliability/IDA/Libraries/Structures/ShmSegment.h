struct shmSegment {
	key_t shmKey;
	int shmId;
	long shmSize;
	char *shmContent;
};