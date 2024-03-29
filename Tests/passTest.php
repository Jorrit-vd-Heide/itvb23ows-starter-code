<?php

use PHPUnit\Framework\TestCase;

class passTest extends TestCase {
    protected $model;
    protected $controllerMock;

    protected function setUp(): void {
        parent::setUp();
        // Arrange: Set up any necessary fixtures or dependencies
        $GLOBALS['OFFSETS'] = [[0, 1], [1, 0], [1, 1], [-1, 0], [0, -1], [-1, -1]];

        // Mock the model and view
        $this->model = $this->getMockBuilder(HiveGameModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $viewMock = $this->createMock(HiveGameView::class);

        $this->controllerMock = $this->getMockBuilder(HiveGameController::class)
            ->setConstructorArgs([$modelMock, $viewMock])
            ->onlyMethods(['pass']) // Mock only the pass method
            ->getMock();
    }

    public function testPassShouldNotBeAllowedWhenMovesOrPlaysAreAvailable() {
        // Arrange: Set up the initial state of the board
        $this->model->board = ['0,0' => 'Q', '0,1' => 'Q', '-1,0' => 'A', '-1,2' => 'B', '-1,-1' => 'S', '-2,2' => 'B'];

        $this->controllerMock->expects($this->once())
                             ->method('pass');

        // Act: Perform the pass
        $this->controllerMock->pass();

        // Assertion: Pass should not be successful and should return errors
        $this->assertTrue($this->controllerMock->hasError());
        $this->assertNotNull($this->controllerMock->getError());
    }

    public function testPassWhenPlayerHasNoMorePlays() {
        // Arrange: Set up empty hands
        $this->model->hand[0] = [];
        $this->model->hand[1] = [];

        $this->controllerMock->expects($this->once())
                             ->method('pass');

        // Act: Perform the pass
        $this->controllerMock->pass();

        // Assertion: Verify that the pass was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }

    public function testPassWhenPlayerHasNoMoreMoves() {
        // Arrange: Set up the initial state of the board
        $this->model->board = [
            '0,0' => 'Q',    // Queen Bee at position (0,0)
            '0,1' => 'A',    // Ant at position (0,1)
            '0,2' => 'A',    // Ant at position (0,2)
            '0,3' => 'S',    // Spider at position (0,3)
            '0,4' => 'B',    // Beetle at position (0,4)
            '0,5' => 'B',    // Beetle at position (0,5)
            '0,6' => 'G',    // Grasshopper at position (0,6)
            '0,-1' => 'G',   // Grasshopper at position (0,-1)
            '1,0' => 'Q',    // Queen Bee at position (1,0)
            '1,1' => 'A',    // Ant at position (1,1)
            '1,2' => 'A',    // Ant at position (1,2)
            '1,3' => 'S',    // Spider at position (1,3)
            '1,4' => 'B',    // Beetle at position (1,4)
            '1,5' => 'B',    // Beetle at position (1,5)
            '1,6' => 'G',    // Grasshopper at position (1,6)
            '1,-1' => 'G',   // Grasshopper at position (1,-1)
            '-1,0' => 'Q',   // Queen Bee at position (-1,0)
            '-1,1' => 'A',   // Ant at position (-1,1)
            '-1,2' => 'A',   // Ant at position (-1,2)
            '-1,3' => 'S',   // Spider at position (-1,3)
            '-1,4' => 'B',   // Beetle at position (-1,4)
            '-1,5' => 'B',   // Beetle at position (-1,5)
            '-1,6' => 'G',   // Grasshopper at position (-1,6)
            '-1,-1' => 'G'   // Grasshopper at position (-1,-1)
        ];
        

        $this->controllerMock->expects($this->once())
                             ->method('pass');

        // Act: Perform the pass
        $this->controllerMock->pass();

        // Assertion: Verify that the pass was successful and there are no errors
        $this->assertFalse($this->controllerMock->hasError());
        $this->assertNull($this->controllerMock->getError());
    }
}
