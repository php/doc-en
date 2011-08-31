<?php
class MyClass {
        public function __destruct() {
                    echo "Destroying object!\n";
                        }
}

$o1 = new MyClass;

$r1 = new Weakref($o1);

echo "Acquiring...\n";
$r1->acquire();

echo "  Unsetting...\n";
unset($o1);

echo "  Acquiring...\n";
$r1->acquire();

echo "    Acquiring...\n";
$r1->acquire();

echo "    Releasing...\n";
$r1->release();

echo "  Releasing...\n";
$r1->release();

echo "Releasing...\n";
$r1->release();

?>
