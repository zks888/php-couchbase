<?php
require_once('CouchbaseTestCase.php');

class ClusterManagerTest extends CouchbaseTestCase {
    /**
     * @test
     * Test that a connection works and return manager instance.
     */
    function testConnect() {
        $h = new \Couchbase\Cluster($this->testDsn);
        $this->assertNotNull($this->testUser);
        $this->assertNotNull($this->testPass);
        $m = $h->manager($this->testUser, $this->testPass);
        return $m;
    }

    /**
     * @depends testConnect
     */
    function testBucketCRUD($m) {
        $name = $this->makeKey('newBucket');
        $m->createBucket($name);
        $buckets = $m->listBuckets();
        $this->assertNotEmpty($buckets);
        $newBucket = NULL;
        foreach ($buckets as $bucket) {
            if ($bucket['name'] == $name) {
                $newBucket = $bucket;
            }
        }
        $this->assertNotNull($newBucket['uuid']);
        $warmup = true;
        while ($warmup) {
            $buckets = $m->listBuckets();
            foreach ($buckets as $bucket) {
                if ($bucket['name'] == $name) {
                    $warmup = false;
                    foreach ($bucket['nodes'] as $node) {
                        if ($node['status'] == 'warmup') {
                            $warmup = true;
                        }
                    }
                    break;
                }
            }
            sleep(1);
        }
        $m->removeBucket($name);
    }
}